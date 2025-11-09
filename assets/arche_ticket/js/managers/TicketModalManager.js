import ApiService from '../services/ApiService.js';
import { CONFIG } from '../config/constants.js';
import { formatDate } from '../utils/format.js';
import { escapeHtml, $, addClass, removeClass } from '../utils/dom.js';
import Logger from '../utils/logger.js';
import EventBus from '../core/EventBus.js';

class TicketModalManager {
    constructor() {
        this.modal = null;
        this.container = null;
        this.title = null;
        this.paginationContainer = null;
        this.detailModal = null;
        this.detailContainer = null;
        this.confirmModal = null;
        this.isLoading = false;
        this.currentFilters = null;
        this.allTickets = [];
        this.filteredTickets = [];
        this.currentPage = 1;
        this.itemsPerPage = 2;
        this.currentTicket = null;
        this.pendingReopenTicketId = null;
        
        this._initElements();
        this._bindEvents();
        this._createConfirmModal();
    }

    /**
     * Initialize DOM elements
     * @private
     */
    _initElements() {
        this.modal = $('#ticketsModal');
        this.container = $('#ticketsContainer');
        this.title = $('#modalTitle');
        this.paginationContainer = $('#ticketsPagination');
        this.detailModal = $('#ticketDetailModal');
        this.detailContainer = $('#ticketDetailContainer');

        if (!this.modal || !this.container || !this.title) {
            Logger.error('Modal elements not found', {
                modal: !!this.modal,
                container: !!this.container,
                title: !!this.title
            });
        }

        if (!this.detailModal || !this.detailContainer) {
            Logger.warn('Detail modal elements not found');
        }
    }

    /**
     * Create confirm modal
     * @private
     */
    _createConfirmModal() {
        // Check if modal already exists
        if (document.getElementById('confirmReopenModal')) {
            this.confirmModal = document.getElementById('confirmReopenModal');
            return;
        }

        const modalHtml = `
            <div id="confirmReopenModal" class="modal modal-confirm">
                <div class="modal-content modal-content-small">
                    <div class="modal-header">
                        <h3><i class="fas fa-question-circle"></i> Confirm Reopen</h3>
                        <button class="modal-close" onclick="TicketModalManager.closeConfirm()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to reopen this ticket?</p>
                        <p class="text-muted small">The ticket status will be changed to "Open".</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="TicketModalManager.closeConfirm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button class="btn btn-success" onclick="TicketModalManager.confirmReopen()">
                            <i class="fas fa-redo"></i> Yes, Reopen
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.confirmModal = document.getElementById('confirmReopenModal');

        // Bind events
        this.confirmModal.addEventListener('click', (e) => {
            if (e.target === this.confirmModal) {
                this.closeConfirm();
            }
        });
    }

    /**
     * Bind modal events
     * @private
     */
    _bindEvents() {
        if (!this.modal) return;

        // Close button for list modal
        const closeBtn = this.modal.querySelector('.modal-close, .close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        // Click outside to close list modal
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Close button for detail modal
        if (this.detailModal) {
            const detailCloseBtn = this.detailModal.querySelector('.modal-close, .close-modal');
            if (detailCloseBtn) {
                detailCloseBtn.addEventListener('click', () => this.closeDetail());
            }

            // Click outside to close detail modal
            this.detailModal.addEventListener('click', (e) => {
                if (e.target === this.detailModal) {
                    this.closeDetail();
                }
            });
        }

        // ESC key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.confirmModal && this.confirmModal.classList.contains('active')) {
                    this.closeConfirm();
                } else if (this.detailModal && this.detailModal.classList.contains('active')) {
                    this.closeDetail();
                } else if (this.modal.classList.contains('active')) {
                    this.close();
                }
            }
        });

        // Listen to ticket created/updated events
        EventBus.on('ticket:created', () => {
            if (this.modal.classList.contains('active')) {
                this.reload();
            }
        });

        EventBus.on('ticket:updated', () => {
            if (this.modal.classList.contains('active')) {
                this.reload();
            }
            if (this.detailModal && this.detailModal.classList.contains('active')) {
                if (this.currentTicket) {
                    this.showDetail(this.currentTicket.id);
                }
            }
        });
    }

    /**
     * Show all tickets
     */
    async showAll() {
        this.title.innerHTML = '<i class="fas fa-ticket-alt"></i> All My Tickets';
        this.currentFilters = null;
        await this.loadAllTickets();
    }

    /**
     * Show tickets filtered by status (client-side filter)
     * @param {string} status - Status to filter by
     */
    async showByStatus(status) {
        const config = CONFIG.STATUS[status];
        
        if (!config) {
            Logger.error('Invalid status:', status);
            return;
        }

        this.title.innerHTML = `<i class="fas ${config.icon}"></i> ${escapeHtml(config.label)}`;
        this.currentFilters = { status };
        
        if (this.allTickets.length > 0) {
            this._applyFilters();
            this.currentPage = 1;
            this._render();
            this.open();
        } else {
            await this.loadAllTickets();
        }
    }

    /**
     * Load all tickets from API (only once)
     */
    async loadAllTickets() {
        if (this.isLoading) {
            Logger.warn('Already loading tickets');
            return;
        }

        this.isLoading = true;
        this.container.innerHTML = this._getLoadingHtml();

        try {
            const response = await ApiService.listTickets({});

            if (!response || typeof response !== 'object') {
                throw new Error('Invalid response format');
            }

            if (!response.success) {
                throw new Error(response.message || 'Failed to load tickets');
            }

            if (!Array.isArray(response.data)) {
                throw new Error('Invalid data format');
            }

            this.allTickets = response.data;
            this._applyFilters();
            this.currentPage = 1;
            this._render();
            this.open();

            EventBus.emit('tickets:loaded', { 
                total: this.allTickets.length,
                filtered: this.filteredTickets.length,
                filters: this.currentFilters 
            });

        } catch (error) {
            Logger.error('Failed to load tickets:', error);
            this.container.innerHTML = this._getErrorHtml(error.message);
            this.open();
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Apply filters to tickets (client-side)
     * @private
     */
    _applyFilters() {
        if (!this.currentFilters || !this.currentFilters.status) {
            this.filteredTickets = [...this.allTickets];
        } else {
            const status = this.currentFilters.status.toLowerCase();
            this.filteredTickets = this.allTickets.filter(ticket => 
                (ticket.status || '').toLowerCase() === status
            );
        }

        Logger.info('Filtered tickets:', {
            total: this.allTickets.length,
            filtered: this.filteredTickets.length,
            filter: this.currentFilters
        });
    }

    /**
     * Reload current tickets view
     */
    async reload() {
        Logger.info('Reloading tickets...');
        
        this.allTickets = [];
        this.filteredTickets = [];
        
        if (this.currentFilters && this.currentFilters.status) {
            await this.showByStatus(this.currentFilters.status);
        } else {
            await this.showAll();
        }
    }

    /**
     * Change page
     * @param {number} page - Page number
     */
    goToPage(page) {
        const totalPages = this._getTotalPages();
        
        if (page < 1 || page > totalPages) {
            Logger.warn('Invalid page number:', page);
            return;
        }

        this.currentPage = page;
        this._render();

        if (this.container) {
            this.container.scrollTop = 0;
        }
    }

    /**
     * Get total pages
     * @private
     */
    _getTotalPages() {
        return Math.ceil(this.filteredTickets.length / this.itemsPerPage);
    }

    /**
     * Get paginated tickets for current page
     * @private
     */
    _getPaginatedTickets() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredTickets.slice(start, end);
    }

    /**
     * Open modal
     */
    open() {
        if (this.modal) {
            addClass(this.modal, 'active');
            document.body.style.overflow = 'hidden';
            
            EventBus.emit('modal:opened', { modal: 'tickets' });
        }
    }

    /**
     * Close modal
     */
    close() {
        if (this.modal) {
            removeClass(this.modal, 'active');
            document.body.style.overflow = '';
            
            EventBus.emit('modal:closed', { modal: 'tickets' });
        }
    }

    /**
     * Navigate to ticket detail page
     * @param {number|string} ticketId - Ticket ID
     */
    viewDetail(ticketId) {
        if (!ticketId || isNaN(ticketId)) {
            Logger.error('Invalid ticket ID:', ticketId);
            return;
        }

        this.showDetail(ticketId);
    }

    /**
     * Show ticket detail modal
     * @param {number|string} ticketId - Ticket ID
     */
    async showDetail(ticketId) {
        if (!this.detailModal || !this.detailContainer) {
            Logger.error('Detail modal not available');
            const url = CONFIG.ENDPOINTS.VIEW + ticketId;
            window.location.href = url;
            return;
        }

        if (!ticketId || isNaN(ticketId)) {
            Logger.error('Invalid ticket ID:', ticketId);
            return;
        }

        this.detailContainer.innerHTML = this._getLoadingHtml();
        this.openDetail();

        try {
            const response = await ApiService.getTicket(ticketId);

            if (!response || typeof response !== 'object') {
                throw new Error('Invalid response format');
            }

            if (!response.success) {
                throw new Error(response.message || 'Failed to load ticket');
            }

            if (!response.data) {
                throw new Error('Ticket not found');
            }

            this.currentTicket = response?.data;
            this._renderDetail(response?.data);

            EventBus.emit('ticket:detail:opened', { ticketId });

        } catch (error) {
            Logger.error('Failed to load ticket detail:', error);
            this.detailContainer.innerHTML = this._getErrorHtml(error.message);
        }
    }

    /**
     * Open detail modal
     */
    openDetail() {
        if (this.detailModal) {
            addClass(this.detailModal, 'active');
            EventBus.emit('modal:opened', { modal: 'ticket-detail' });
        }
    }

    /**
     * Close detail modal
     */
    closeDetail() {
        if (this.detailModal) {
            removeClass(this.detailModal, 'active');
            this.currentTicket = null;
            EventBus.emit('modal:closed', { modal: 'ticket-detail' });
        }
    }

    /**
     * Show confirm modal for reopening ticket
     * @param {number|string} ticketId - Ticket ID
     */
    reopenTicket(ticketId, event) {
        if (event) {
            event.stopPropagation();
        }

        if (!ticketId || isNaN(ticketId)) {
            Logger.error('Invalid ticket ID:', ticketId);
            return;
        }

        this.pendingReopenTicketId = ticketId;
        this.openConfirm();
    }

    /**
     * Open confirm modal
     */
    openConfirm() {
        if (this.confirmModal) {
            addClass(this.confirmModal, 'active');
            EventBus.emit('modal:opened', { modal: 'confirm-reopen' });
        }
    }

    /**
     * Close confirm modal
     */
    closeConfirm() {
        if (this.confirmModal) {
            removeClass(this.confirmModal, 'active');
            this.pendingReopenTicketId = null;
            EventBus.emit('modal:closed', { modal: 'confirm-reopen' });
        }
    }

    /**
     * Confirm and execute reopen
     */
    async confirmReopen() {
        if (!this.pendingReopenTicketId) {
            Logger.error('No pending ticket to reopen');
            this.closeConfirm();
            return;
        }

        const ticketId = this.pendingReopenTicketId;
        this.closeConfirm();

        try {
            const response = await ApiService.updateTicket(ticketId, 'open');

            if (!response || !response.success) {
                throw new Error(response?.message || 'Failed to reopen ticket');
            }

            Logger.info('Ticket reopened successfully:', ticketId);
            
            await this.reload();

            if (this.detailModal && this.detailModal.classList.contains('active')) {
                this.closeDetail();
            }

            EventBus.emit('ticket:reopened', { ticketId });
            this._showSuccessMessage('Ticket reopened successfully!');
            
        } catch (error) {
            Logger.error('Failed to reopen ticket:', error);
            this._showErrorMessage('Failed to reopen ticket: ' + error.message);
        }
    }

    /**
     * Show success message
     * @private
     */
    _showSuccessMessage(message) {
        // You can implement a toast notification system here
        // For now, using alert
        alert(message);
    }

    /**
     * Show error message
     * @private
     */
    _showErrorMessage(message) {
        // You can implement a toast notification system here
        // For now, using alert
        alert(message);
    }

    /**
     * Add response to ticket
     * @param {number|string} ticketId - Ticket ID
     */
    async addResponse(ticketId, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!ticketId || isNaN(ticketId)) {
            Logger.error('Invalid ticket ID:', ticketId);
            return;
        }

        const responseText = prompt('Enter your response:');
        
        if (!responseText || responseText.trim() === '') {
            return;
        }

        try {
            const response = await ApiService.addTicketResponse(ticketId, {
                message: responseText.trim()
            });

            if (!response || !response.success) {
                throw new Error(response?.message || 'Failed to add response');
            }

            Logger.info('Response added successfully:', ticketId);
            
            await this.showDetail(ticketId);

            EventBus.emit('ticket:response:added', { ticketId });
            this._showSuccessMessage('Response added successfully!');
            
        } catch (error) {
            Logger.error('Failed to add response:', error);
            this._showErrorMessage('Failed to add response: ' + error.message);
        }
    }

    /**
     * Render tickets list with pagination
     * @private
     */
    _render() {
        if (this.filteredTickets.length === 0) {
            this.container.innerHTML = this._getEmptyHtml();
            if (this.paginationContainer) {
                this.paginationContainer.innerHTML = '';
            }
            return;
        }

        const paginatedTickets = this._getPaginatedTickets();
        const html = paginatedTickets.map(ticket => this._renderTicketItem(ticket)).join('');
        this.container.innerHTML = html;

        this._renderPagination();
        this._attachTicketHandlers();
    }

    /**
     * Render pagination controls
     * @private
     */
    _renderPagination() {
        if (!this.paginationContainer) return;

        const totalPages = this._getTotalPages();
        
        if (totalPages <= 1) {
            this.paginationContainer.innerHTML = '';
            return;
        }

        const start = (this.currentPage - 1) * this.itemsPerPage + 1;
        const end = Math.min(this.currentPage * this.itemsPerPage, this.filteredTickets.length);

        let html = `
            <div class="pagination-info">
                Showing ${start}-${end} of ${this.filteredTickets.length} tickets
            </div>
            <div class="pagination-controls">
        `;

        html += `
            <button 
                class="btn btn-sm btn-pagination ${this.currentPage === 1 ? 'disabled' : ''}"
                onclick="TicketModalManager.goToPage(${this.currentPage - 1})"
                ${this.currentPage === 1 ? 'disabled' : ''}
            >
                <i class="fas fa-chevron-left"></i>
            </button>
        `;

        const maxVisible = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        if (startPage > 1) {
            html += `<button class="btn btn-sm btn-pagination" onclick="TicketModalManager.goToPage(1)">1</button>`;
            if (startPage > 2) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <button 
                    class="btn btn-sm btn-pagination ${i === this.currentPage ? 'active' : ''}"
                    onclick="TicketModalManager.goToPage(${i})"
                >
                    ${i}
                </button>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
            html += `<button class="btn btn-sm btn-pagination" onclick="TicketModalManager.goToPage(${totalPages})">${totalPages}</button>`;
        }

        html += `
            <button 
                class="btn btn-sm btn-pagination ${this.currentPage === totalPages ? 'disabled' : ''}"
                onclick="TicketModalManager.goToPage(${this.currentPage + 1})"
                ${this.currentPage === totalPages ? 'disabled' : ''}
            >
                <i class="fas fa-chevron-right"></i>
            </button>
        `;

        html += `</div>`;
        
        this.paginationContainer.innerHTML = html;
    }

    /**
     * Attach click handlers to ticket items
     * @private
     */
    _attachTicketHandlers() {
        const ticketItems = this.container.querySelectorAll('.ticket-item');
        
        ticketItems.forEach(item => {
            const ticketId = item.dataset.ticketId;
            
            if (ticketId) {
                item.addEventListener('click', (e) => {
                    if (!e.target.closest('.ticket-actions')) {
                        this.viewDetail(ticketId);
                    }
                });

                item.setAttribute('tabindex', '0');
                item.setAttribute('role', 'button');
                
                item.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.viewDetail(ticketId);
                    }
                });
            }
        });

        const detailButtons = this.container.querySelectorAll('.btn-detail');
        detailButtons.forEach(btn => {
            const ticketId = btn.dataset.ticketId;
            if (ticketId) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.viewDetail(ticketId);
                });
            }
        });

        const reopenButtons = this.container.querySelectorAll('.btn-reopen');
        reopenButtons.forEach(btn => {
            const ticketId = btn.dataset.ticketId;
            if (ticketId) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.reopenTicket(ticketId, e);
                });
            }
        });
    }

    /**
     * Render single ticket item
     * @private
     */
    _renderTicketItem(ticket) {
        if (!ticket || typeof ticket !== 'object') {
            Logger.warn('Invalid ticket object:', ticket);
            return '';
        }

        const ticketId = parseInt(ticket.id);
        if (isNaN(ticketId)) {
            Logger.warn('Invalid ticket ID:', ticket.id);
            return '';
        }

        const status = (ticket.status || 'unknown').toLowerCase();
        const statusConfig = CONFIG.STATUS[status];
        const statusIcon = statusConfig?.icon || 'fa-circle';
        const statusLabel = statusConfig?.label || ticket.status || 'Unknown';

        const title = escapeHtml(ticket.title || ticket.name || 'Untitled');
        const category = escapeHtml(ticket.category || 'General');
        const createdDate = formatDate(ticket.created_date || ticket.created_at);
        const priority = ticket.priority ? escapeHtml(ticket.priority) : null;

        const showReopenBtn = ['closed', 'resolved', 'cancelled'].includes(status);

        return `
            <div class="ticket-item ${status}" data-ticket-id="${ticketId}">
                <div class="ticket-content">
                    <div class="ticket-header">
                        <span class="ticket-id">#${ticketId}</span>
                        <h4 class="ticket-title">${title}</h4>
                    </div>
                    <div class="ticket-meta">
                        <div class="ticket-meta-item">
                            <i class="fas fa-folder"></i>
                            <span>${category}</span>
                        </div>
                        <div class="ticket-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>${createdDate}</span>
                        </div>
                        ${priority ? `
                            <div class="ticket-meta-item">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>${priority}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
                <div class="ticket-actions">
                    <button 
                        class="btn btn-sm view-btn-detail btn-detail" 
                        data-ticket-id="${ticketId}"
                        title="View Details"
                    >
                        <i class="fas fa-eye"></i> Detail
                    </button>
                    ${showReopenBtn ? `
                        <button 
                            class="btn btn-sm btn-success btn-reopen" 
                            data-ticket-id="${ticketId}"
                            title="Reopen Ticket"
                        >
                            <i class="fas fa-redo"></i> Reopen
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }

    /**
     * Get loading HTML
     * @private
     */
    _getLoadingHtml() {
        return `
            <div class="modal-loading">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p>Loading tickets...</p>
            </div>
        `;
    }

    /**
     * Get error HTML
     * @private
     */
    _getErrorHtml(message = 'An error occurred') {
        return `
            <div class="empty-tickets error">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Error Loading Tickets</h3>
                <p>${escapeHtml(message)}</p>
                <button class="btn btn-primary" onclick="TicketModalManager.reload()">
                    <i class="fas fa-sync"></i> Retry
                </button>
            </div>
        `;
    }

    /**
     * Get empty state HTML
     * @private
     */
    _getEmptyHtml() {
        const filterMsg = this.currentFilters?.status 
            ? `with status "${this.currentFilters.status}"`
            : '';

        return `
            <div class="empty-tickets">
                <i class="fas fa-inbox"></i>
                <h3>No Tickets Found</h3>
                <p>No tickets found ${filterMsg}</p>
                ${this.currentFilters ? `
                    <button class="btn btn-secondary" onclick="TicketModalManager.showAll()">
                        <i class="fas fa-list"></i> View All Tickets
                    </button>
                ` : ''}
            </div>
        `;
    }

    /**
     * Render ticket detail
     * @private
     */
    _renderDetail(ticket) {
        if (!ticket || typeof ticket !== 'object') {
            Logger.error('Invalid ticket data');
            this.detailContainer.innerHTML = this._getErrorHtml('Invalid ticket data');
            return;
        }

        const ticketId = parseInt(ticket.id);
        const status = (ticket.status || 'unknown').toLowerCase();
        const statusConfig = CONFIG.STATUS[status];
        const statusIcon = statusConfig?.icon || 'fa-circle';
        const statusLabel = statusConfig?.label || ticket.status || 'Unknown';

        const title = escapeHtml(ticket.name || 'Untitled');
        const category = escapeHtml(ticket.category || 'General');
        const priority = ticket.priority ? escapeHtml(ticket.priority) : 'Normal';
        const description = escapeHtml(ticket.content || 'No description provided');
        const createdDate = ticket.date_creation
            ? formatDate(ticket.date_creation) 
            : '';
        const updatedDate = ticket.date_mod
            ? formatDate(ticket.date_mod) 
            : '';

        const showReopenBtn = ['closed', 'resolved', 'cancelled'].includes(status);

        let html = `
            <div class="ticket-detail">
                <div class="ticket-detail-header">
                    <div class="ticket-detail-title-section">
                        <span class="ticket-id-badge">#${ticketId}</span>
                        <h2 class="ticket-detail-title">${title}</h2>
                    </div>
                    <div class="ticket-detail-actions">
                        ${showReopenBtn ? `
                            <button 
                                class="btn btn-success btn-reopen-detail" 
                                onclick="TicketModalManager.reopenTicket(${ticketId}, event)"
                            >
                                <i class="fas fa-redo"></i> Reopen
                            </button>
                        ` : ''}
                    </div>
                </div>

                <div class="ticket-detail-meta">
                    <div class="ticket-detail-meta-item">
                        <label>Status:</label>
                        <span class="status-badge ${status}">
                            <i class="fas ${statusIcon}"></i> ${escapeHtml(statusLabel)}
                        </span>
                    </div>
                    <div class="ticket-detail-meta-item">
                        <label>Category:</label>
                        <span><i class="fas fa-folder"></i> ${category}</span>
                    </div>
                    <div class="ticket-detail-meta-item">
                        <label>Priority:</label>
                        <span><i class="fas fa-exclamation-circle"></i> ${priority}</span>
                    </div>
                    <div class="ticket-detail-meta-item">
                        <label>Created:</label>
                        <span><i class="fas fa-calendar-plus"></i> ${createdDate}</span>
                    </div>
                    <div class="ticket-detail-meta-item">
                        <label>Updated:</label>
                        <span><i class="fas fa-calendar-check"></i> ${updatedDate}</span>
                    </div>
                </div>

                <div class="ticket-detail-section">
                    <h3><i class="fas fa-align-left"></i> Description</h3>
                    <div class="ticket-detail-content">
                        ${description.replace(/\n/g, '<br>')}
                    </div>
                </div>
        `;

        if (ticket.responses && Array.isArray(ticket.responses) && ticket.responses.length > 0) {
            html += `
                <div class="ticket-detail-section">
                    <h3><i class="fas fa-comments"></i> Responses (${ticket.responses.length})</h3>
                    <div class="ticket-responses">
                        ${ticket.responses.map(response => this._renderResponse(response)).join('')}
                    </div>
                </div>
            `;
        }

        if (ticket.attachments && Array.isArray(ticket.attachments) && ticket.attachments.length > 0) {
            html += `
                <div class="ticket-detail-section">
                    <h3><i class="fas fa-paperclip"></i> Attachments (${ticket.attachments.length})</h3>
                    <div class="ticket-attachments">
                        ${ticket.attachments.map(att => this._renderAttachment(att)).join('')}
                    </div>
                </div>
            `;
        }

        html += `</div>`;

        this.detailContainer.innerHTML = html;
    }

    /**
     * Render single response
     * @private
     */
    _renderResponse(response) {
        if (!response || typeof response !== 'object') {
            return '';
        }

        const author = escapeHtml(response.author || response.user || 'Unknown');
        const message = escapeHtml(response.message || response.text || '');
        const date = formatDate(response.created_at || response.date);
        const isStaff = response.is_staff || response.type === 'staff';

        return `
            <div class="ticket-response ${isStaff ? 'staff-response' : 'user-response'}">
                <div class="response-header">
                    <div class="response-author">
                        <i class="fas ${isStaff ? 'fa-user-shield' : 'fa-user'}"></i>
                        <strong>${author}</strong>
                        ${isStaff ? '<span class="staff-badge">Staff</span>' : ''}
                    </div>
                    <div class="response-date">
                        <i class="fas fa-clock"></i> ${date}
                    </div>
                </div>
                <div class="response-content">
                    ${message.replace(/\n/g, '<br>')}
                </div>
            </div>
        `;
    }

    /**
     * Render single attachment
     * @private
     */
    _renderAttachment(attachment) {
        if (!attachment || typeof attachment !== 'object') {
            return '';
        }

        const name = escapeHtml(attachment.name || attachment.filename || 'Unknown file');
        const size = this._formatFileSize(attachment.size || 0);
        const mime = attachment.mime || 'application/octet-stream';
        const icon = this._getFileIcon(mime);
        const docId = attachment.id;
        
        const downloadUrl = CONFIG.ENDPOINTS.DOWNLOAD_DOCUMENT_TICKET + docId;

        return `
            <div class="attachment-item">
                <div class="attachment-icon">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="attachment-info">
                    <div class="attachment-name" title="${name}">
                        ${name}
                    </div>
                    <div class="attachment-meta">
                        <span class="attachment-size">${size}</span>
                        <span class="attachment-type">${this._getFileType(mime)}</span>
                    </div>
                </div>
                <div class="attachment-actions">
                    <a 
                        href="${downloadUrl}" 
                        class="btn btn-sm btn-primary" 
                        download
                        title="Download"
                    >
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
        `;
    }

    /**
     * Format file size
     * @private
     */
    _formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * Get file icon based on mime type
     * @private
     */
    _getFileIcon(mime) {
        if (!mime) return 'fa-file';
        
        const iconMap = {
            'image/': 'fa-file-image',
            'video/': 'fa-file-video',
            'audio/': 'fa-file-audio',
            'application/pdf': 'fa-file-pdf',
            'application/msword': 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml': 'fa-file-word',
            'application/vnd.ms-excel': 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml': 'fa-file-excel',
            'application/vnd.ms-powerpoint': 'fa-file-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml': 'fa-file-powerpoint',
            'application/zip': 'fa-file-archive',
            'application/x-rar': 'fa-file-archive',
            'text/': 'fa-file-alt'
        };

        for (let key in iconMap) {
            if (mime.startsWith(key)) {
                return iconMap[key];
            }
        }
        
        return 'fa-file';
    }

    /**
     * Get file type label
     * @private
     */
    _getFileType(mime) {
        if (!mime) return 'File';
        
        const parts = mime.split('/');
        if (parts.length > 1) {
            return parts[1].toUpperCase();
        }
        
        return 'File';
    }
}

// Create instance and expose globally
const modalManager = new TicketModalManager();
window.TicketModalManager = modalManager;

export default modalManager;