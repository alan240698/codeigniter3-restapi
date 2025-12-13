import { escapeHtml } from '../../../utils/dom.js';

class TemplateRenderer {
    /**
     * Get loading HTML
     */
    static getLoadingHtml() {
        return `
            <div class="modal-loading">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p>Loading tickets...</p>
            </div>
        `;
    }

    /**
     * Get error HTML
     */
    static getErrorHtml(message = 'An error occurred') {
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
     */
    static getEmptyHtml(currentFilters = null) {
        const filterMsg = currentFilters?.status
            ? `with status "${currentFilters.status}"`
            : '';

        return `
            <div class="empty-tickets">
                <i class="fas fa-inbox"></i>
                <h3>No Tickets Found</h3>
                <p>No tickets found ${filterMsg}</p>
                ${currentFilters ? `
                    <button class="btn btn-secondary" onclick="TicketModalManager.showAll()">
                        <i class="fas fa-list"></i> View All Tickets
                    </button>
                ` : ''}
            </div>
        `;
    }
}

export default TemplateRenderer;