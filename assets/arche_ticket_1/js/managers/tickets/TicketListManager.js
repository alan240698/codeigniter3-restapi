import { $, addClass, removeClass } from '../../utils/dom.js';
import Logger from '../../utils/logger.js';
import EventBus from '../../core/EventBus.js';
import TicketItemRenderer from './renderers/TicketItemRenderer.js';
import PaginationRenderer from './renderers/PaginationRenderer.js';
import TemplateRenderer from './renderers/TemplateRenderer.js';

class TicketListManager {
    constructor() {
        this.modal = null;
        this.container = null;
        this.title = null;
        this.paginationContainer = null;
        this.isLoading = false;

        this._initElements();
        this._bindEvents();
    }

    /**
     * Initialize DOM elements
     */
    _initElements() {
        this.modal = $('#ticketsModal');
        this.container = $('#ticketsContainer');
        this.title = $('#modalTitle');
        this.paginationContainer = $('#ticketsPagination');

        if (!this.modal || !this.container || !this.title) {
            Logger.error('Modal elements not found', {
                modal: !!this.modal,
                container: !!this.container,
                title: !!this.title
            });
        }
    }

    /**
     * Bind modal events
     */
    _bindEvents() {
        if (!this.modal) return;

        const closeBtn = this.modal.querySelector('.modal-close, .close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
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
     * Set title
     */
    setTitle(html) {
        if (this.title) {
            this.title.innerHTML = html;
        }
    }

    /**
     * Show loading
     */
    showLoading() {
        this.isLoading = true;
        this.container.innerHTML = TemplateRenderer.getLoadingHtml();
    }

    /**
     * Show error
     */
    showError(message) {
        this.isLoading = false;
        this.container.innerHTML = TemplateRenderer.getErrorHtml(message);
    }

    /**
     * Render tickets list
     */
    render(tickets, currentPage, totalPages, filteredCount, itemsPerPage, currentFilters) {
        this.isLoading = false;

        if (tickets.length === 0) {
            this.container.innerHTML = TemplateRenderer.getEmptyHtml(currentFilters);
            if (this.paginationContainer) {
                this.paginationContainer.innerHTML = '';
            }
            return;
        }

        const html = tickets.map(ticket => TicketItemRenderer.render(ticket)).join('');
        this.container.innerHTML = html;

        this._renderPagination(currentPage, totalPages, filteredCount, itemsPerPage);
    }

    /**
     * Render pagination
     */
    _renderPagination(currentPage, totalPages, filteredCount, itemsPerPage) {
        if (!this.paginationContainer) return;

        const html = PaginationRenderer.render(currentPage, totalPages, filteredCount, itemsPerPage);
        this.paginationContainer.innerHTML = html;
    }

    /**
     * Attach event handlers to ticket items
     */
    attachHandlers(onViewDetail, onReopen) {
        const ticketItems = this.container.querySelectorAll('.ticket-item');

        ticketItems.forEach(item => {
            const ticketId = item.dataset.ticketId;

            if (ticketId) {
                item.addEventListener('click', (e) => {
                    if (!e.target.closest('.ticket-actions')) {
                        onViewDetail(ticketId);
                    }
                });

                item.setAttribute('tabindex', '0');
                item.setAttribute('role', 'button');

                item.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        onViewDetail(ticketId);
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
                    onViewDetail(ticketId);
                });
            }
        });

        const reopenButtons = this.container.querySelectorAll('.btn-reopen');
        reopenButtons.forEach(btn => {
            const ticketId = btn.dataset.ticketId;
            if (ticketId) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    onReopen(ticketId, e);
                });
            }
        });
    }

    /**
     * Scroll to top
     */
    scrollToTop() {
        if (this.container) {
            this.container.scrollTop = 0;
        }
    }

    /**
     * Check if loading
     */
    isCurrentlyLoading() {
        return this.isLoading;
    }
}

export default TicketListManager;
