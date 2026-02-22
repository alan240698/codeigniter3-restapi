/**
 * Ticket Management System
 * Handles tab switching, ticket loading, and Kanban board rendering
 */

const TicketManagement = {
    currentTab: 'created',
    currentView: 'kanban',
    tickets: [],

    /**
     * Initialize the ticket management system
     */
    init() {
        console.log('Initializing Ticket Management System...');
        this.loadCounts();
        this.loadTickets('created');
    },

    /**
     * Load ticket counts for all tabs
     */
    async loadCounts() {
        try {
            const response = await fetch(`${BASE_URL}api/it_ticket/ticket-management/counts?employee_id=${EMPLOYEE_ID}`);
            const data = await response.json();

            if (data.success) {
                document.getElementById('created-count').textContent = data.counts.created || 0;
                document.getElementById('assigned-count').textContent = data.counts.assigned || 0;
                document.getElementById('team-count').textContent = data.counts.team || 0;
            }
        } catch (error) {
            console.error('Error loading counts:', error);
        }
    },

    /**
     * Switch between tabs
     */
    switchTab(tab) {
        // Update active tab
        document.querySelectorAll('.tab-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`.tab-item[data-tab="${tab}"]`).classList.add('active');

        // Update current tab
        this.currentTab = tab;

        // Load tickets for this tab
        this.loadTickets(tab);
    },

    /**
     * Switch between views (Kanban/Table)
     */
    switchView(view) {
        // Update active view button
        document.querySelectorAll('.view-btn[data-view]').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`.view-btn[data-view="${view}"]`).classList.add('active');

        // Show/hide views
        if (view === 'kanban') {
            document.getElementById('kanban-view').style.display = 'grid';
            document.getElementById('table-view').style.display = 'none';
        } else {
            document.getElementById('kanban-view').style.display = 'none';
            document.getElementById('table-view').style.display = 'block';
        }

        this.currentView = view;
    },

    /**
     * Load tickets for a specific tab
     */
    async loadTickets(tab) {
        this.showLoading(true);

        try {
            const endpoint = `${BASE_URL}api/it_ticket/ticket-management/my-${tab}?employee_id=${EMPLOYEE_ID}`;
            const response = await fetch(endpoint);
            const data = await response.json();

            if (data.success) {
                this.tickets = data.tickets || [];
                this.renderKanban();
            } else {
                console.error('Failed to load tickets:', data.message);
                this.showEmptyState();
            }
        } catch (error) {
            console.error('Error loading tickets:', error);
            this.showEmptyState();
        } finally {
            this.showLoading(false);
        }
    },

    /**
     * Render Kanban board
     */
    renderKanban() {
        const statuses = ['PROCESSING', 'PENDING', 'SOLVED', 'CLOSED'];

        statuses.forEach(status => {
            const columnId = `column-${status.toLowerCase()}`;
            const column = document.getElementById(columnId);
            const countId = `count-${status.toLowerCase()}`;

            // Filter tickets by status
            const filtered = this.tickets.filter(t =>
                (t.status_name || '').toUpperCase() === status
            );

            // Update count
            document.getElementById(countId).textContent = filtered.length;

            // Render tickets
            if (filtered.length === 0) {
                column.innerHTML = this.createEmptyState();
            } else {
                column.innerHTML = filtered.map(ticket =>
                    this.createTicketCard(ticket, status.toLowerCase())
                ).join('');
            }
        });
    },

    /**
     * Create a ticket card HTML
     */
    createTicketCard(ticket, status) {
        const teamName = ticket.team_name || 'Group IT';
        const title = ticket.title || ticket.issue_type_name || 'Untitled';
        const description = ticket.description || 'No description';
        const assignedName = ticket.assigned_name || 'Unassigned';
        const createdDate = this.formatDate(ticket.created_at);
        const priority = ticket.priority || '';

        // Get initials for avatar
        const initials = assignedName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

        return `
            <div class="ticket-card ${status}" onclick="TicketManagement.openTicket(${ticket.id})">
                <div class="team-badge">${teamName}</div>
                
                <h6 class="ticket-title">${this.escapeHtml(title)}</h6>
                <p class="ticket-desc">${this.escapeHtml(description)}</p>
                
                <div class="ticket-meta">
                    <div class="assigned-to">
                        <div class="avatar">${initials}</div>
                        <span class="assigned-name">${this.escapeHtml(assignedName)}</span>
                    </div>
                    <div class="ticket-date">${createdDate}</div>
                </div>
                
                <div class="ticket-stats">
                    <span><i class="far fa-comment"></i> ${ticket.comments_count || 0}</span>
                    <span><i class="fas fa-link"></i> ${ticket.links_count || 0}</span>
                    <span><i class="far fa-file"></i> ${ticket.attachments_count || 0}</span>
                </div>
                
                ${priority === 'High' ? '<span class="priority-badge">High</span>' : ''}
                
                ${ticket.notes ? `
                    <div class="notes-section">
                        <strong>Notes:</strong>
                        <ul>
                            ${ticket.notes.split('\n').map(note => `<li>${this.escapeHtml(note)}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            </div>
        `;
    },

    /**
     * Create empty state HTML
     */
    createEmptyState() {
        return `
            <div class="empty-column">
                <i class="fas fa-inbox"></i>
                <p>No tickets</p>
            </div>
        `;
    },

    /**
     * Show/hide loading overlay
     */
    showLoading(show) {
        const overlay = document.getElementById('loading-overlay');
        const kanban = document.getElementById('kanban-view');

        if (show) {
            overlay.style.display = 'flex';
            kanban.style.opacity = '0.5';
        } else {
            overlay.style.display = 'none';
            kanban.style.opacity = '1';
        }
    },

    /**
     * Show empty state when no tickets
     */
    showEmptyState() {
        const statuses = ['processing', 'pending', 'solved', 'closed'];
        statuses.forEach(status => {
            const column = document.getElementById(`column-${status}`);
            column.innerHTML = this.createEmptyState();
            document.getElementById(`count-${status}`).textContent = '0';
        });
    },

    /**
     * Open ticket detail (placeholder)
     */
    openTicket(ticketId) {
        console.log('Opening ticket:', ticketId);

        Swal.fire({
            title: 'Ticket Detail',
            text: `Ticket ID: ${ticketId}`,
            icon: 'info',
            confirmButtonText: 'Close',
            confirmButtonColor: '#26c6da'
        });

        // TODO: Open ticket detail modal
    },

    /**
     * Format date
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';

        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));

        if (days === 0) {
            const hours = Math.floor(diff / (1000 * 60 * 60));
            if (hours === 0) {
                const minutes = Math.floor(diff / (1000 * 60));
                return `${minutes} min ago`;
            }
            return `${hours}h ago`;
        } else if (days === 1) {
            return 'Yesterday';
        } else if (days < 7) {
            return `${days} days ago`;
        } else {
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }
    },

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};
