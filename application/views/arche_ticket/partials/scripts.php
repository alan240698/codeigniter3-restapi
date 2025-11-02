<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
const CONFIG = {
    BASE_URL: '<?= base_url() ?>',
    ENDPOINTS: {
        CREATE: '<?= base_url() ?>arche_ticket/ticket/create',
        LIST: '<?= base_url() ?>arche_ticket/ticket/list',
        VIEW: '<?= base_url() ?>arche_ticket/ticket/view/'
    },
    STATUS: {
        pending: { icon: 'fa-clock', label: 'Pending Tickets' },
        processing: { icon: 'fa-spinner', label: 'Processing Tickets' },
        solved: { icon: 'fa-check-circle', label: 'Solved Tickets' },
        closed: { icon: 'fa-archive', label: 'Closed Tickets' }
    }
};

// ========== Statistics Manager ==========
const Statistics = {
    load() {
        $.ajax({
            url: CONFIG.ENDPOINTS.LIST,
            method: 'GET',
            dataType: 'json',
            success: (response) => {
                if (response.success && response.data) {
                    this.update(response.data);
                }
            }
        });
    },

    update(tickets) {
        const counts = { pending: 0, processing: 0, solved: 0, closed: 0 };
        
        tickets.forEach(ticket => {
            const status = ticket.status.toLowerCase();
            if (counts.hasOwnProperty(status)) {
                counts[status]++;
            }
        });

        Object.keys(counts).forEach(status => {
            $(`#${status}Count`).text(counts[status]);
        });
    }
};

// ========== Ticket Modal Manager ==========
const TicketModal = {
    showAll() {
        $('#modalTitle').html('<i class="fas fa-ticket-alt"></i> All My Tickets');
        this.loadTickets();
    },

    showByStatus(status) {
        const config = CONFIG.STATUS[status];
        $('#modalTitle').html(`<i class="fas ${config.icon}"></i> ${config.label}`);
        this.loadTickets(status);
    },

    loadTickets(status = null) {
        $('#ticketsContainer').html(this.getLoadingHtml());

        $.ajax({
            url: CONFIG.ENDPOINTS.LIST,
            method: 'GET',
            dataType: 'json',
            data: status ? { status } : {},
            success: (response) => {
                if (response.success && response.data) {
                    this.render(response.data);
                    this.open();
                }
            },
            error: () => {
                $('#ticketsContainer').html(this.getErrorHtml());
                this.open();
            }
        });
    },

    render(tickets) {
        const container = $('#ticketsContainer');

        if (tickets.length === 0) {
            container.html(this.getEmptyHtml());
            return;
        }

        const html = tickets.map(ticket => this.renderTicketItem(ticket)).join('');
        container.html(html);
    },

    renderTicketItem(ticket) {
        const status = ticket.status.toLowerCase();
        const statusIcon = CONFIG.STATUS[status]?.icon || 'fa-circle';
        
        return `
            <div class="ticket-item ${status}" onclick="TicketModal.viewDetail(${ticket.id})">
                <div class="ticket-header">
                    <h4 class="ticket-title">${Utils.escapeHtml(ticket.title || ticket.name)}</h4>
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
                        <span>${Utils.escapeHtml(ticket.category)}</span>
                    </div>
                    <div class="ticket-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>${Utils.formatDate(ticket.created_date)}</span>
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
    },

    open() {
        $('#ticketsModal').addClass('active');
    },

    close() {
        $('#ticketsModal').removeClass('active');
    },

    viewDetail(ticketId) {
        window.location.href = CONFIG.ENDPOINTS.VIEW + ticketId;
    },

    getLoadingHtml() {
        return '<div style="text-align:center;padding:3rem;"><i class="fas fa-spinner fa-spin fa-3x" style="color:#667eea;"></i></div>';
    },

    getErrorHtml() {
        return `
            <div class="empty-tickets">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Error loading tickets</h3>
                <p>Please try again later</p>
            </div>
        `;
    },

    getEmptyHtml() {
        return `
            <div class="empty-tickets">
                <i class="fas fa-inbox"></i>
                <h3>No tickets found</h3>
                <p>You haven't created any tickets yet</p>
            </div>
        `;
    }
};

// ========== Utilities ==========
const Utils = {
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));

        if (diffDays === 0) return 'Today';
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays} days ago`;

        return date.toLocaleDateString('vi-VN');
    }
};

// ========== Toast Notifications ==========
const Toast = {
    show(message, type = 'success') {
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        const toast = $(`
            <div class="toast-notification ${type}">
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            </div>
        `);

        $('#toastContainer').append(toast);
        setTimeout(() => toast.addClass('show'), 10);
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
};

// ========== Event Handlers ==========
$(document).ready(function() {
    Statistics.load();
    <?php include(APPPATH . 'views/arche_ticket/js/dashboard_scripts.js'); ?>

    // Modal click outside to close
    $('#ticketsModal').click(function(e) {
        if (e.target === this) TicketModal.close();
    });

    // ESC key to close modal
    $(document).keydown(function(e) {
        if (e.key === 'Escape') TicketModal.close();
    });
});

// ========== Global Functions (for backward compatibility) ==========
function showToast(message, type) { Toast.show(message, type); }
function loadTicketStatistics() { Statistics.load(); }
</script>
</body>
</html>