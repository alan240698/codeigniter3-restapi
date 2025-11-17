import { addClass, removeClass }    from '../../utils/dom.js';
import Logger                       from '../../utils/logger.js';
import EventBus                     from '../../core/EventBus.js';

class TicketConfirmManager {
    constructor() {
        this.modal = null;
        this.pendingTicketId = null;
        this._createModal();
        this._bindEvents();
    }

    /**
     * Create confirm modal
     */
    _createModal() {
        if (document.getElementById('confirmReopenModal')) {
            this.modal = document.getElementById('confirmReopenModal');
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
                        <p class="text-muted small">The ticket status will be changed</p>
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
        this.modal = document.getElementById('confirmReopenModal');
    }

    /**
     * Bind events
     */
    _bindEvents() {
        if (!this.modal) return;

        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
    }

    /**
     * Open confirm modal
     */
    open(ticketId) {
        if (!ticketId || isNaN(ticketId)) {
            Logger.error('Invalid ticket ID:', ticketId);
            return;
        }

        this.pendingTicketId = ticketId;

        if (this.modal) {
            addClass(this.modal, 'active');
            EventBus.emit('modal:opened', { modal: 'confirm-reopen' });
        }
    }

    /**
     * Close confirm modal
     */
    close() {
        if (this.modal) {
            removeClass(this.modal, 'active');
            this.pendingTicketId = null;
            EventBus.emit('modal:closed', { modal: 'confirm-reopen' });
        }
    }

    /**
     * Get pending ticket ID
     */
    getPendingTicketId() {
        return this.pendingTicketId;
    }
}

export default TicketConfirmManager;