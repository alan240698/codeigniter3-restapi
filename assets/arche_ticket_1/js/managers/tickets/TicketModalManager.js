import { CONFIG } from '../../config/constants.js';
import { escapeHtml } from '../../utils/dom.js';
import Logger from '../../utils/logger.js';
import EventBus from '../../core/EventBus.js';
import ApiService from '../../services/api/index.js';
import TicketFilterManager from './TicketFilterManager.js';
import TicketPaginationManager from './TicketPaginationManager.js';
import TicketListManager from './TicketListManager.js';
import TicketDetailManager from './TicketDetailManager.js';
import TicketConfirmManager from './TicketConfirmManager.js';

class TicketModalManager {
    constructor() {
        this.filterManager = new TicketFilterManager();
        this.paginationManager = new TicketPaginationManager(5);
        this.listManager = new TicketListManager();
        this.detailManager = new TicketDetailManager();
        this.confirmManager = new TicketConfirmManager();

        this._bindGlobalEvents();
    }

    /**
     * Bind global events
     */
    _bindGlobalEvents() {
        // ESC key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.confirmManager.modal && this.confirmManager.modal.classList.contains('active')) {
                    this.closeConfirm();
                } else if (this.detailManager.modal && this.detailManager.modal.classList.contains('active')) {
                    this.closeDetail();
                } else if (this.listManager.modal && this.listManager.modal.classList.contains('active')) {
                    this.close();
                }
            }
        });

        // Listen to ticket events
        EventBus.on('ticket:created', () => {
            if (this.listManager.modal.classList.contains('active')) {
                this.reload();
            }
        });

        EventBus.on('ticket:updated', () => {
            if (this.listManager.modal.classList.contains('active')) {
                this.reload();
            }
            if (this.detailManager.modal && this.detailManager.modal.classList.contains('active')) {
                this.detailManager.reload();
            }
        });
    }

    /**
     * Show all tickets
     */
    async showAll() {
        this.listManager.setTitle('<i class="fas fa-ticket-alt"></i> All My Tickets');
        this.filterManager.clearFilters();
        await this.loadAllTickets();
    }

    /**
     * Show tickets filtered by status
     */
    async showByStatus(status) {
        const config = CONFIG.STATUS[status];
        
        if (!config) {
            Logger.error('Invalid status:', status);
            return;
        }

        this.listManager.setTitle(`<i class="fas ${config.icon}"></i> ${escapeHtml(config.label)}`);
        this.filterManager.setFilters({ status });
        
        const allTickets = this.filterManager.allTickets;
        if (allTickets.length > 0) {
            this.paginationManager.reset();
            this._render();
            this.listManager.open();
        } else {
            await this.loadAllTickets();
        }
    }

    /**
     * Load all tickets from API
     */
    async loadAllTickets() {
        if (this.listManager.isCurrentlyLoading()) {
            Logger.warn('Already loading tickets');
            return;
        }

        this.listManager.showLoading();

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

            this.filterManager.setAllTickets(response.data);
            this.paginationManager.reset();
            this._render();
            this.listManager.open();

            EventBus.emit('tickets:loaded', { 
                total: response.data.length,
                filtered: this.filterManager.getFilteredTickets().length,
                filters: this.filterManager.getCurrentFilters() 
            });

        } catch (error) {
            Logger.error('Failed to load tickets:', error);
            this.listManager.showError(error.message);
            this.listManager.open();
        }
    }

    /**
     * Reload current tickets view
     */
    async reload() {
        Logger.info('Reloading tickets...');
        
        this.filterManager.setAllTickets([]);

        const currentFilters = this.filterManager.getCurrentFilters();
        if (currentFilters && currentFilters.status) {
            await this.showByStatus(currentFilters.status);
        } else {
            await this.showAll();
        }
    }

    /**
     * Go to page
     */
    goToPage(page) {
        const filteredTickets = this.filterManager.getFilteredTickets();
        const success = this.paginationManager.goToPage(page, filteredTickets.length);

        if (success) {
            this._render();
            this.listManager.scrollToTop();
        }
    }

    /**
     * Render tickets list
     */
    _render() {
        const filteredTickets = this.filterManager.getFilteredTickets();
        const paginatedTickets = this.paginationManager.getPaginatedItems(filteredTickets);
        const currentPage = this.paginationManager.getCurrentPage();
        const totalPages = this.paginationManager.getTotalPages(filteredTickets.length);
        const currentFilters = this.filterManager.getCurrentFilters();

        this.listManager.render(
            paginatedTickets,
            currentPage,
            totalPages,
            filteredTickets.length,
            this.paginationManager.itemsPerPage,
            currentFilters
        );

        this.listManager.attachHandlers(
            (ticketId) => this.viewDetail(ticketId),
            (ticketId, event) => this.reopenTicket(ticketId, event)
        );
    }

    /**
     * Open list modal
     */
    open() {
        this.listManager.open();
    }

    /**
     * Close list modal
     */
    close() {
        this.listManager.close();
    }

    /**
     * View ticket detail
     */
    viewDetail(ticketId) {
        if (!ticketId || isNaN(ticketId)) {
            Logger.error('Invalid ticket ID:', ticketId);
            return;
        }

        this.detailManager.showDetail(ticketId);
    }

    /**
     * Show ticket detail
     */
    async showDetail(ticketId) {
        await this.detailManager.showDetail(ticketId);
    }

    /**
     * Open detail modal
     */
    openDetail() {
        this.detailManager.open();
    }

    /**
     * Close detail modal
     */
    closeDetail() {
        this.detailManager.close();
    }

    /**
     * Reopen ticket
     */
    reopenTicket(ticketId, event) {
        if (event) {
            event.stopPropagation();
        }

        if (!ticketId || isNaN(ticketId)) {
            Logger.error('Invalid ticket ID:', ticketId);
            return;
        }

        this.confirmManager.open(ticketId);
    }

    /**
     * Open confirm modal
     */
    openConfirm() {
        // Called from HTML onclick
        const ticketId = this.confirmManager.getPendingTicketId();
        if (ticketId) {
            this.confirmManager.open(ticketId);
        }
    }

    /**
     * Close confirm modal
     */
    closeConfirm() {
        this.confirmManager.close();
    }

    /**
     * Confirm and execute reopen
     */
    async confirmReopen() {
        const ticketId = this.confirmManager.getPendingTicketId();
        
        if (!ticketId) {
            Logger.error('No pending ticket to reopen');
            this.closeConfirm();
            return;
        }

        this.closeConfirm();

        try {
            const response = await ApiService.updateTicket(ticketId, 'open');

            if (!response || !response.success) {
                throw new Error(response?.message || 'Failed to reopen ticket');
            }

            Logger.info('Ticket reopened successfully:', ticketId);
            
            await this.reload();

            if (this.detailManager.modal && this.detailManager.modal.classList.contains('active')) {
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
     * Add response to ticket
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

        const result = await this.detailManager.addResponse(ticketId, responseText);

        if (result.success) {
            this._showSuccessMessage('Response added successfully!');
        } else {
            this._showErrorMessage('Failed to add response: ' + result.error);
        }
    }

    /**
     * Show success message
     */
    _showSuccessMessage(message) {
        // TODO: Implement toast notification system
        alert(message);
    }

    /**
     * Show error message
     */
    _showErrorMessage(message) {
        // TODO: Implement toast notification system
        alert(message);
    }
}

// Create instance and expose globally
const modalManager = new TicketModalManager();
window.TicketModalManager = modalManager;

export default modalManager;