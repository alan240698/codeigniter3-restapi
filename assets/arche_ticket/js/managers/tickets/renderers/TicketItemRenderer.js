import { CONFIG } from '../../../config/constants.js';
import { formatDate } from '../../../utils/format.js';
import { escapeHtml } from '../../../utils/dom.js';
import Logger from '../../../utils/logger.js';

class TicketItemRenderer {
    /**
     * Render single ticket item
     */
    static render(ticket) {
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
}

export default TicketItemRenderer;