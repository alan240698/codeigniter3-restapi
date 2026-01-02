/**
 * Assigned Tickets Manager
 * Handles loading and updating assigned tickets
 */

const AssignedTicketsManager = {
    currentPage: 1,
    perPage: 10,
    totalRecords: 0,
    tickets: [],

    /**
     * Initialize assigned tickets tab
     */
    init() {
        console.log('Initializing Assigned Tickets Manager...');

        // Load tickets when tab is shown
        document.getElementById('my-assigned-tab').addEventListener('shown.bs.tab', () => {
            this.loadTickets();
        });

        // Refresh button
        document.getElementById('btn-refresh-assigned')?.addEventListener('click', () => {
            this.loadTickets();
        });

        // Pagination
        document.getElementById('assigned-entries-per-page')?.addEventListener('change', (e) => {
            this.perPage = parseInt(e.target.value);
            this.currentPage = 1;
            this.loadTickets();
        });

        // Update ticket button
        document.getElementById('btn-update-ticket')?.addEventListener('click', () => {
            this.updateTicket();
        });

        // Load dropdowns for update modal
        this.loadTeamsDropdown();
        this.loadUsersDropdown();
        this.loadStatusesDropdown();

        // Load tickets immediately for testing
        this.loadTickets();
    },

    /**
     * Load assigned tickets from API
     */
    async loadTickets() {
        this.showLoading(true);

        try {
            // TEMPORARY: Using dummy data for testing modal
            // TODO: Replace with real API call when ready
            const dummyData = {
                success: true,
                tickets: [
                    {
                        id: 1,
                        title: '[Vietnam] - Group website > Bug, error, permission',
                        description: 'Improve the user experience and visual appeal of the bottom sheet',
                        service_name: 'IT Support',
                        requester_name: 'John Doe',
                        priority: 'High',
                        status_name: 'In Progress',
                        status_id: 2,
                        assigned_team_id: 1,
                        assigned_to: 5,
                        created_at: '2026-01-02 10:30:00'
                    },
                    {
                        id: 2,
                        title: '[Vietnam] - network > Unable to access some websites',
                        description: 'Create wireframes and high-fidelity designs for the app main screens',
                        service_name: 'Network',
                        requester_name: 'Jane Smith',
                        priority: 'Medium',
                        status_name: 'Pending',
                        status_id: 3,
                        assigned_team_id: 2,
                        assigned_to: 3,
                        created_at: '2026-01-01 14:20:00'
                    },
                    {
                        id: 3,
                        title: 'Email configuration issue',
                        description: 'Unable to send emails from Outlook',
                        service_name: 'Email Support',
                        requester_name: 'Bob Johnson',
                        priority: 'Low',
                        status_name: 'Open',
                        status_id: 1,
                        assigned_team_id: 1,
                        assigned_to: 5,
                        created_at: '2025-12-30 09:15:00'
                    }
                ]
            };

            // Use dummy data
            const data = dummyData;

            /* Real API call - uncomment when ready:
            const response = await fetch(`${BASE_URL}api/it_ticket/ticket-management/my-assigned?employee_id=${EMPLOYEE_ID}`);
            const data = await response.json();
            */

            if (data.success) {
                this.tickets = data.tickets || [];
                this.totalRecords = this.tickets.length;
                this.renderTable();
            } else {
                console.error('Failed to load tickets:', data.message);
                this.showEmpty();
            }
        } catch (error) {
            console.error('Error loading assigned tickets:', error);
            this.showEmpty();
        } finally {
            this.showLoading(false);
        }
    },

    /**
     * Render tickets table
     */
    renderTable() {
        const tbody = document.getElementById('assigned-table-body');
        const container = document.getElementById('assigned-table-container');
        const empty = document.getElementById('assigned-empty');

        if (this.tickets.length === 0) {
            container.style.display = 'none';
            empty.style.display = 'block';
            this.updatePaginationInfo(0, 0, 0);
            return;
        }

        container.style.display = 'block';
        empty.style.display = 'none';

        // Calculate pagination
        const start = (this.currentPage - 1) * this.perPage;
        const end = Math.min(start + this.perPage, this.totalRecords);
        const pageTickets = this.tickets.slice(start, end);

        // Render rows
        tbody.innerHTML = pageTickets.map(ticket => this.createTableRow(ticket)).join('');

        // Update pagination
        this.updatePaginationInfo(start + 1, end, this.totalRecords);
    },

    /**
     * Create table row HTML
     */
    createTableRow(ticket) {
        const statusClass = this.getStatusClass(ticket.status_name);
        const priorityClass = this.getPriorityClass(ticket.priority);

        return `
            <tr>
                <td class="text-center"><strong>#${ticket.id}</strong></td>
                <td>${this.escapeHtml(ticket.service_name || 'N/A')}</td>
                <td>${this.escapeHtml(ticket.title || ticket.issue_type_name || 'Untitled')}</td>
                <td>${this.escapeHtml(ticket.requester_name || 'Unknown')}</td>
                <td>
                    <span class="badge ${priorityClass}">${ticket.priority || 'Medium'}</span>
                </td>
                <td>
                    <span class="badge ${statusClass}">${ticket.status_name || 'Open'}</span>
                </td>
                <td>${this.formatDate(ticket.created_at)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary" onclick="AssignedTicketsManager.openUpdateModal(${ticket.id})">
                        <i class="fas fa-edit"></i> Update
                    </button>
                </td>
            </tr>
        `;
    },

    /**
     * Open update ticket modal
     */
    async openUpdateModal(ticketId) {
        try {
            // TEMPORARY: Using dummy data for testing
            // TODO: Replace with real API call
            const dummyTicket = {
                success: true,
                data: {
                    id: ticketId,
                    title: '[Vietnam] - Group website > Bug, error, permission',
                    description: 'Improve the user experience and visual appeal of the bottom sheet to improve accessibility. Prioritize flexibility on multiple content-layouts.',
                    assigned_team_id: 1,
                    assigned_to: 5,
                    priority: 'High',
                    status_id: 2
                }
            };

            const data = dummyTicket;

            /* Real API call - uncomment when ready:
            const response = await fetch(`${BASE_URL}api/tickets/show/${ticketId}`);
            const data = await response.json();
            */

            if (data.success) {
                const ticket = data.data;

                // Populate modal
                document.getElementById('update-ticket-id').value = ticket.id;
                document.getElementById('update-ticket-title').textContent = ticket.title || 'Ticket #' + ticket.id;
                document.getElementById('update-ticket-description').textContent = ticket.description || '';
                document.getElementById('update-assigned-team').value = ticket.assigned_team_id || '';
                document.getElementById('update-assigned-to').value = ticket.assigned_to || '';
                document.getElementById('update-priority').value = ticket.priority || 'Medium';
                // document.getElementById('update-status').value = ticket.status_id || ''; // Field removed in new modal
                document.getElementById('update-comment').value = '';
                // document.getElementById('update-notes').value = ''; // Field removed in new modal

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('updateTicketModal'));
                modal.show();
            }
        } catch (error) {
            console.error('Error loading ticket:', error);
            Swal.fire('Error', 'Failed to load ticket details', 'error');
        }
    },

    /**
     * Update ticket
     */
    async updateTicket() {
        const ticketId = document.getElementById('update-ticket-id').value;
        const formData = {
            assigned_team_id: document.getElementById('update-assigned-team').value || null,
            assigned_to: document.getElementById('update-assigned-to').value || null,
            priority: document.getElementById('update-priority').value,
            status_id: document.getElementById('update-status').value,
            comment: document.getElementById('update-comment').value,
            notes: document.getElementById('update-notes').value
        };

        try {
            const response = await fetch(`${BASE_URL}api/tickets/update/${ticketId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire('Success', 'Ticket updated successfully', 'success');

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('updateTicketModal')).hide();

                // Reload tickets
                this.loadTickets();
            } else {
                Swal.fire('Error', data.message || 'Failed to update ticket', 'error');
            }
        } catch (error) {
            console.error('Error updating ticket:', error);
            Swal.fire('Error', 'Failed to update ticket', 'error');
        }
    },

    /**
     * Load teams dropdown
     */
    async loadTeamsDropdown() {
        try {
            const response = await fetch(`${BASE_URL}support-teams`);
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById('update-assigned-team');
                select.innerHTML = '<option value="">Select Team</option>' +
                    data.data.map(team => `<option value="${team.id}">${team.name}</option>`).join('');
            }
        } catch (error) {
            console.error('Error loading teams:', error);
        }
    },

    /**
     * Load users dropdown
     */
    async loadUsersDropdown() {
        try {
            const response = await fetch(`${BASE_URL}employees/active`);
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById('update-assigned-to');
                select.innerHTML = '<option value="">Select User</option>' +
                    data.map(user => `<option value="${user.employee_id}">${user.fullname}</option>`).join('');
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    },

    /**
     * Load statuses dropdown
     */
    async loadStatusesDropdown() {
        // For now, hardcoded statuses - can be fetched from API later
        const statuses = [
            { id: 1, name: 'Open' },
            { id: 2, name: 'In Progress' },
            { id: 3, name: 'Pending' },
            { id: 4, name: 'Resolved' },
            { id: 5, name: 'Closed' }
        ];

        const select = document.getElementById('update-status');
        select.innerHTML = '<option value="">Select Status</option>' +
            statuses.map(status => `<option value="${status.id}">${status.name}</option>`).join('');
    },

    /**
     * Show/hide loading
     */
    showLoading(show) {
        const loading = document.getElementById('assigned-loading');
        const container = document.getElementById('assigned-table-container');

        if (show) {
            loading.style.display = 'block';
            container.style.opacity = '0.5';
        } else {
            loading.style.display = 'none';
            container.style.opacity = '1';
        }
    },

    /**
     * Show empty state
     */
    showEmpty() {
        document.getElementById('assigned-table-container').style.display = 'none';
        document.getElementById('assigned-empty').style.display = 'block';
        this.updatePaginationInfo(0, 0, 0);
    },

    /**
     * Update pagination info
     */
    updatePaginationInfo(start, end, total) {
        document.getElementById('assigned-table-info').textContent =
            `Showing ${start} to ${end} of ${total} entries`;
    },

    /**
     * Get status badge class
     */
    getStatusClass(status) {
        const statusMap = {
            'Open': 'bg-info',
            'In Progress': 'bg-primary',
            'Pending': 'bg-warning',
            'Resolved': 'bg-success',
            'Closed': 'bg-secondary'
        };
        return statusMap[status] || 'bg-secondary';
    },

    /**
     * Get priority badge class
     */
    getPriorityClass(priority) {
        const priorityMap = {
            'Low': 'bg-success',
            'Medium': 'bg-info',
            'High': 'bg-warning',
            'Critical': 'bg-danger'
        };
        return priorityMap[priority] || 'bg-info';
    },

    /**
     * Format date
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    AssignedTicketsManager.init();
});
