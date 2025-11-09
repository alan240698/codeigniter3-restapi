import { $, addClass, removeClass } from '../../utils/dom.js';
import { CONFIG } from '../../config/constants.js';
import Logger from '../../utils/logger.js';
import EventBus from '../../core/EventBus.js';
import ApiService from '../../services/api/index.js';
import TicketDetailRenderer from './renderers/TicketDetailRenderer.js';
import TemplateRenderer from './renderers/TemplateRenderer.js';

class TicketDetailManager {
    constructor() {
        this.modal = null;
        this.container = null;
        this.currentTicket = null;

        this._initElements();
        this._bindEvents();
    }

    /**
     * Initialize DOM elements
     */
    _initElements() {
        this.modal = $('#ticketDetailModal');
        this.container = $('#ticketDetailContainer');

        if (!this.modal || !this.container) {
            Logger.warn('Detail modal elements not found');
        }
    }

    /**
     * Bind events
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
            EventBus.emit('modal:opened', { modal: 'ticket-detail' });
        }
    }

    /**
     * Close modal
     */
    close() {
        if (this.modal) {
            removeClass(this.modal, 'active');
            this.currentTicket = null;
            EventBus.emit('modal:closed', { modal: 'ticket-detail' });
        }
    }

    /**
     * Show ticket detail
     */
    async showDetail(ticketId) {
        if (!this.modal || !this.container) {
            Logger.error('Detail modal not available');
            const url = CONFIG.ENDPOINTS.VIEW + ticketId;
            window.location.href = url;
            return;
        }

        if (!ticketId || isNaN(ticketId)) {
            Logger.error('Invalid ticket ID:', ticketId);
            return;
        }

        this.container.innerHTML = TemplateRenderer.getLoadingHtml();
        this.open();

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

            this.currentTicket = response.data;
            this.container.innerHTML = TicketDetailRenderer.render(response.data);

            EventBus.emit('ticket:detail:opened', { ticketId });

        } catch (error) {
            Logger.error('Failed to load ticket detail:', error);
            this.container.innerHTML = TemplateRenderer.getErrorHtml(error.message);
        }
    }

    /**
     * Reload current ticket
     */
    async reload() {
        if (this.currentTicket) {
            await this.showDetail(this.currentTicket.id);
        }
    }

    /**
     * Get current ticket
     */
    getCurrentTicket() {
        return this.currentTicket;
    }

    /**
     * Add response to ticket
     */
    async addResponse(ticketId, message) {
        try {
            const response = await ApiService.addTicketResponse(ticketId, {
                message: message.trim()
            });

            if (!response || !response.success) {
                throw new Error(response?.message || 'Failed to add response');
            }

            Logger.info('Response added successfully:', ticketId);
            await this.showDetail(ticketId);

            EventBus.emit('ticket:response:added', { ticketId });
            return { success: true };

        } catch (error) {
            Logger.error('Failed to add response:', error);
            return { success: false, error: error.message };
        }
    }
}

export default TicketDetailManager;