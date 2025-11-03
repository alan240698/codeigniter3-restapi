import ApiService from '../services/ApiService.js';
import { CONFIG } from '../config/constants.js';
import { formatDate } from '../utils/format.js';
import { escapeHtml, } from '../utils/dom.js';
import { $, addClass, removeClass } from '../utils/dom.js';
import Logger from '../utils/logger.js';

class TicketModalManager {
    constructor() {
        this.modal = $('#ticketsModal');
        this.container = $('#ticketsContainer');
        this.title = $('#modalTitle');
    }

    async showAll() {
        this.title.innerHTML = '<i class="fas fa-ticket-alt"></i> All My Tickets';
        await this.loadTickets();
    }

    async showByStatus(status) {
        const config = CONFIG.STATUS[status];
        this.title.innerHTML = `<i class="fas ${config.icon}"></i> ${config.label}`;
        await this.loadTickets(status);
    }

    async loadTickets(status = null) {
        this.container.innerHTML = this._getLoadingHtml();

        try {
            const filters = status ? { status } : {};
            const response = await ApiService.listTickets(filters);

            if (response.success && response.data) {
                this._render(response.data);
                this.open();
            }
        } catch (error) {
            Logger.error('Failed to load tickets:', error);
            this.container.innerHTML = this._getErrorHtml();
            this.open();
        }
    }

    open() {
        if (this.modal) {
            addClass(this.modal, 'active');
        }
    }

    close() {
        if (this.modal) {
            removeClass(this.modal, 'active');
        }
    }

    viewDetail(ticketId) {
        window.location.href = CONFIG.ENDPOINTS.VIEW + ticketId;
    }

    _render(tickets) {
        if (tickets.length === 0) {
            this.container.innerHTML = this._getEmptyHtml();
            return;
        }

        const html = tickets.map(ticket => this._renderTicketItem(ticket)).join('');
        this.container.innerHTML = html;
    }

    _renderTicketItem(ticket) {
        const status = ticket.status.toLowerCase();
        const statusIcon = CONFIG.STATUS[status]?.icon || 'fa-circle';

        return `
            <div class="ticket-item ${status}" onclick="TicketModalManager.viewDetail(${ticket.id})">
                <div class="ticket-header">
                    <h4 class="ticket-title">${escapeHtml(ticket.title || ticket.name)}</h4>
                    <span class="ticket-id">#${ticket.id}</span>
                </div>
                <div class="ticket-meta">
                    <div class="ticket-meta-item">
                        <span class="status-badge ${status}">
                            <i class="fas ${statusIcon}"></i> ${ticket.status}
                        </span>
                    </div>
                    <div class="ticket-meta-item">
                        <i class="fas fa-folder"></i>
                        <span>${escapeHtml(ticket.category)}</span>
                    </div>
                    <div class="ticket-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>${formatDate(ticket.created_date)}</span>
                    </div>
                    ${ticket.priority ? `
                        <div class="ticket-meta-item">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>${ticket.priority}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    _getLoadingHtml() {
        return `
            <div style="text-align:center;padding:3rem;">
                <i class="fas fa-spinner fa-spin fa-3x" style="color:#667eea;"></i>
            </div>
        `;
    }

    _getErrorHtml() {
        return `
            <div class="empty-tickets">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Error loading tickets</h3>
                <p>Please try again later</p>
            </div>
        `;
    }

    _getEmptyHtml() {
        return `
            <div class="empty-tickets">
                <i class="fas fa-inbox"></i>
                <h3>No tickets found</h3>
                <p>You haven't created any tickets yet</p>
            </div>
        `;
    }
}

// Create instance and expose globally for onclick handlers
const modalManager = new TicketModalManager();
window.TicketModalManager = modalManager;

export default modalManager;
