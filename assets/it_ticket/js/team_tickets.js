/**
 * TeamTicketsManager
 * Handles team unassigned tickets (tickets available to pick)
 */

const TeamTicketsManager = {
    currentPage: 1,
    perPage: 10,
    totalRecords: 0,
    tickets: [],
    currentTicketId: null,

    /**
     * Initialize team tickets tab
     */
    init() {
        console.log('Initializing Team Tickets Manager...');

        // Load tickets when tab is shown
        document.getElementById('my-assigned-tab')?.addEventListener('shown.bs.tab', () => {
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

        // Pagination buttons
        document.getElementById('assigned-page-prev')?.addEventListener('click', (e) => {
            e.preventDefault();
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadTickets();
            }
        });

        document.getElementById('assigned-page-next')?.addEventListener('click', (e) => {
            e.preventDefault();
            const totalPages = Math.ceil(this.totalRecords / this.perPage);
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.loadTickets();
            }
        });

        // Update ticket button
        document.getElementById('btn-update-ticket')?.addEventListener('click', () => {
            this.updateTicket();
        });

        // Assign to me links
        document.getElementById('assign-to-me')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('update-assigned-to').value = EMPLOYEE_ID;
        });

        document.getElementById('assign-observer-to-me')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('update-observer').value = EMPLOYEE_ID;
        });

        // Add note button
        document.getElementById('btn-add-note')?.addEventListener('click', () => {
            this.addPrivateNote();
        });

        // Submit comment button
        document.getElementById('btn-submit-comment')?.addEventListener('click', () => {
            this.submitComment();
        });

        // Load dropdowns for update modal
        this.loadTeamsDropdown();
        this.loadUsersDropdown();

        // Load tickets if on team tab
        if (document.getElementById('my-assigned')?.classList.contains('active')) {
            this.loadTickets();
        }
    },

    /**
     * Load team unassigned tickets from API
     */
    async loadTickets() {
        this.showLoading(true);

        const params = new URLSearchParams({
            page: this.currentPage,
            per_page: this.perPage,
            employee_id: EMPLOYEE_ID,
            country: COUNTRY
        });

        try {
            const response = await fetch(`${BASE_URL}api/tickets/team-unassigned?${params}`);
            const data = await response.json();

            if (data.success) {
                this.tickets = data.data || [];
                this.totalRecords = data.total || 0;
                this.renderTable();
                this.updatePaginationButtons(data.total_pages || 1);
            } else {
                console.error('Failed to load tickets:', data.message);
                this.showEmpty();
            }
        } catch (error) {
            console.error('Error loading team tickets:', error);
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
            this.updateTableInfo(0, 0, 0);
            return;
        }

        container.style.display = 'block';
        empty.style.display = 'none';

        // Render rows
        tbody.innerHTML = this.tickets.map(ticket => this.createTableRow(ticket)).join('');

        // Update pagination info
        const start = (this.currentPage - 1) * this.perPage + 1;
        const end = Math.min(this.currentPage * this.perPage, this.totalRecords);
        this.updateTableInfo(start, end, this.totalRecords);
    },

    /**
     * Create table row HTML
     */
    createTableRow(ticket) {
        const statusClass = this.getStatusColor(ticket.status);
        const priorityClass = this.getPriorityClass(ticket.priority);

        return `
            <tr style="cursor: pointer;" onclick="TeamTicketsManager.openUpdateModal(${ticket.id})">
                <td class="text-center"><strong>#${ticket.id}</strong></td>
                <td>${this.escapeHtml(ticket.it_service_name || ticket.service_name || 'N/A')}</td>
                <td>${this.truncateText(ticket.subject || ticket.description || 'Untitled', 50)}</td>
                <td>${this.escapeHtml(ticket?.requester_name || 'Unknown')}</td>
                <td>
                    <span class="badge bg-${priorityClass}">${ticket.priority || 'Medium'}</span>
                </td>
                <td>
                    <span class="badge bg-${statusClass}">${ticket.status || 'New'}</span>
                </td>
                <td>${this.formatDate(ticket.created_at)}</td>
                <td class="text-center" onclick="event.stopPropagation();">
                    <button class="btn btn-sm btn-success me-1" onclick="TeamTicketsManager.pickTicket(${ticket.id})">
                        <i class="fas fa-hand-pointer"></i> Pick
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="TeamTicketsManager.openUpdateModal(${ticket.id})">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `;
    },

    /**
     * Pick/claim ticket
     */
    async pickTicket(ticketId) {
        const result = await Swal.fire({
            title: 'Pick Ticket?',
            text: 'Do you want to pick this ticket and assign it to yourself?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, pick it!'
        });

        if (result.isConfirmed) {
            try {
                Swal.fire({
                    title: 'Picking ticket...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const response = await fetch(`${BASE_URL}api/tickets/${ticketId}/pick`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: EMPLOYEE_ID
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Success', 'Ticket picked successfully!', 'success');
                    this.loadTickets(); // Reload table
                } else {
                    Swal.fire('Error', data.message || 'Failed to pick ticket', 'error');
                }
            } catch (error) {
                console.error('Error picking ticket:', error);
                Swal.fire('Error', 'Failed to pick ticket', 'error');
            }
        }
    },

    /**
     * Open update ticket modal - Load ticket details by ID
     */
    async openUpdateModal(ticketId) {
        try {
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch(`${BASE_URL}api/tickets/show/${ticketId}`);
            const data = await response.json();

            Swal.close();

            if (!data.success || !data.data) {
                Swal.fire('Error', 'Ticket not found', 'error');
                return;
            }

            const ticketData = data.data;
            const ticket = ticketData.ticket;
            this.currentTicketId = ticketId;

            // Populate modal - Header
            document.getElementById('update-ticket-badge').textContent = this.getPriorityBadgeText(ticket.priority);
            document.getElementById('update-ticket-number').textContent = ticket.ticket_number || `ID-${String(ticket.id).padStart(3, '0')}`;

            // Populate modal - Main Content
            document.getElementById('update-ticket-id').value = ticket.id;
            document.getElementById('update-ticket-title').textContent = ticket.subject || ticket.description || 'Ticket #' + ticket.id;
            document.getElementById('update-ticket-description').textContent = ticket.description || '';

            // Attachments
            const attachments = ticketData.attachments || [];
            const attachmentsSection = document.getElementById('attachments-section');
            if (attachments.length > 0) {
                attachmentsSection.style.display = 'block';
                document.getElementById('attachment-count').textContent = attachments.length;
                document.getElementById('attachments-container').innerHTML = attachments.map(att => `
                    <div class="col-md-6">
                        <div class="border rounded p-2">
                            <div class="fw-bold small text-truncate">${this.escapeHtml(att.filename || att.file_name)}</div>
                            <div class="text-muted small">${this.formatFileSize(att.size || att.file_size)} 
                                <a href="${BASE_URL}${att.file_path || att.url}" class="text-primary" target="_blank">View file</a>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                attachmentsSection.style.display = 'none';
            }

            // Comments
            const comments = ticketData.comments || [];
            document.getElementById('comments-count').textContent = comments.length;
            if (comments.length > 0) {
                document.getElementById('comments-list').innerHTML = comments.map(comment => `
                    <div class="d-flex gap-3 mb-4">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong class="small">${this.escapeHtml(comment.fullname || comment.user_name)}</strong>
                                <span class="text-muted small">• ${this.formatTimeAgo(comment.created_at)}</span>
                            </div>
                            <p class="small mb-0 text-muted">${this.escapeHtml(comment.comment || comment.content)}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                document.getElementById('comments-list').innerHTML = '<p class="text-muted small">No comments yet.</p>';
            }

            // Activity Logs
            const logs = ticketData.history || [];
            document.getElementById('logs-count').textContent = logs.length;
            if (logs.length > 0) {
                document.getElementById('logs-list').innerHTML = logs.map(log => `
                    <div class="d-flex gap-2 mb-3 small">
                        <div class="text-muted">${this.formatDate(log.created_at)}</div>
                        <div><strong>${log.action}</strong>: ${this.escapeHtml(log.comment || log.description || '')}</div>
                    </div>
                `).join('');
            } else {
                document.getElementById('logs-list').innerHTML = '<p class="text-muted small">No activity logs yet.</p>';
            }

            // Left Sidebar Details
            document.getElementById('update-country').value = ticket.country || COUNTRY;
            document.getElementById('update-assigned-team').value = ticket.assigned_team_id || '';
            document.getElementById('update-assigned-to').value = ticket.assigned_to || '';
            document.getElementById('update-observer').value = ticket.observer_id || '';

            // Right Sidebar Details
            document.getElementById('update-priority').value = ticket.priority || 'medium';
            document.getElementById('update-status-display').textContent = ticket.status || '—';
            document.getElementById('update-opening-date').textContent = this.formatDate(ticket.created_at);

            // Private Notes (from it_ticket_notes table)
            // For now, we'll query notes separately or include in API
            document.getElementById('private-notes-list').innerHTML = '<p class="text-muted small">No notes yet.</p>';

            // Clear comment textarea
            document.getElementById('update-comment').value = '';

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('updateTicketModal'));
            modal.show();

        } catch (error) {
            Swal.close();
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
            country: document.getElementById('update-country').value,
            assigned_team_id: document.getElementById('update-assigned-team').value || null,
            assigned_to: document.getElementById('update-assigned-to').value || null,
            observer_id: document.getElementById('update-observer').value || null,
            priority: document.getElementById('update-priority').value,
            employee_id: EMPLOYEE_ID
        };

        Swal.fire({
            title: 'Updating...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const response = await fetch(`${BASE_URL}api/tickets/${ticketId}/update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire('Success', data.message || 'Ticket updated successfully', 'success');
                bootstrap.Modal.getInstance(document.getElementById('updateTicketModal')).hide();
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
     * Submit comment
     */
    async submitComment() {
        const ticketId = this.currentTicketId;
        const comment = document.getElementById('update-comment').value.trim();

        if (!comment) {
            Swal.fire('Warning', 'Please enter a comment', 'warning');
            return;
        }

        try {
            const response = await fetch(`${BASE_URL}api/tickets/${ticketId}/comment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    comment: comment,
                    employee_id: EMPLOYEE_ID 
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire('Success', 'Comment added successfully', 'success');
                document.getElementById('update-comment').value = '';
                this.openUpdateModal(ticketId);
            } else {
                Swal.fire('Error', data.message || 'Failed to add comment', 'error');
            }
        } catch (error) {
            console.error('Error adding comment:', error);
            Swal.fire('Error', 'Failed to add comment', 'error');
        }
    },

    /**
     * Add private note
     */
    async addPrivateNote() {
        const { value: note } = await Swal.fire({
            title: 'Add Private Note',
            input: 'textarea',
            inputPlaceholder: 'Enter your note...',
            showCancelButton: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'Please enter a note';
                }
            }
        });

        if (note) {
            try {
                const response = await fetch(`${BASE_URL}api/tickets/${this.currentTicketId}/note`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        note: note,
                        employee_id: EMPLOYEE_ID 
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Success', 'Note added successfully', 'success');
                    this.openUpdateModal(this.currentTicketId);
                } else {
                    Swal.fire('Error', data.message || 'Failed to add note', 'error');
                }
            } catch (error) {
                console.error('Error adding note:', error);
                Swal.fire('Error', 'Failed to add note', 'error');
            }
        }
    },

    /**
     * Delete note
     */
    async deleteNote(noteId) {
        const result = await Swal.fire({
            title: 'Delete Note?',
            text: 'Are you sure you want to delete this note?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`${BASE_URL}api/tickets/notes/${noteId}`, {
                    method: 'DELETE'
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Deleted!', 'Note has been deleted.', 'success');
                    this.openUpdateModal(this.currentTicketId);
                } else {
                    Swal.fire('Error', data.message || 'Failed to delete note', 'error');
                }
            } catch (error) {
                console.error('Error deleting note:', error);
                Swal.fire('Error', 'Failed to delete note', 'error');
            }
        }
    },

    /**
     * Load teams dropdown
     */
    async loadTeamsDropdown() {
        try {
            const response = await fetch(`${BASE_URL}api/support-teams`);
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById('update-assigned-team');
                select.innerHTML = '<option value="">Select Team</option>' +
                    data.data.map(team => `<option value="${team.id}">${this.escapeHtml(team.name)}</option>`).join('');
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
            const response = await fetch(`${BASE_URL}api/employees/active`);
            const data = await response.json();

            if (data.success) {
                const assignedSelect = document.getElementById('update-assigned-to');
                const observerSelect = document.getElementById('update-observer');
                
                const options = '<option value="">Select User</option>' +
                    data.data.map(user => `<option value="${user.employee_id}">${this.escapeHtml(user.fullname)}</option>`).join('');
                
                assignedSelect.innerHTML = options;
                observerSelect.innerHTML = options;
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    },

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

    showEmpty() {
        document.getElementById('assigned-table-container').style.display = 'none';
        document.getElementById('assigned-empty').style.display = 'block';
        this.updateTableInfo(0, 0, 0);
        this.updatePaginationButtons(1);
    },

    updateTableInfo(start, end, total) {
        document.getElementById('assigned-table-info').textContent =
            `Showing ${start} to ${end} of ${total} entries`;
    },

    updatePaginationButtons(totalPages) {
        const prevBtn = document.getElementById('assigned-page-prev');
        const nextBtn = document.getElementById('assigned-page-next');
        if (this.currentPage <= 1) {
            prevBtn.classList.add('disabled');
        } else {
            prevBtn.classList.remove('disabled');
        }
        if (this.currentPage >= totalPages) {
            nextBtn.classList.add('disabled');
        } else {
            nextBtn.classList.remove('disabled');
        }
    },

    getStatusColor(status) {
        const colors = {
            'new': 'primary', 'open': 'info', 'assigned': 'info',
            'in_progress': 'warning', 'processing': 'warning', 'pending': 'warning',
            'resolved': 'success', 'solved': 'success', 'closed': 'secondary',
            'pending_approval': 'warning'
        };
        return colors[status?.toLowerCase()] || 'secondary';
    },

    getPriorityClass(priority) {
        const priorityMap = {
            'low': 'success', 'medium': 'info', 'normal': 'info',
            'high': 'warning', 'critical': 'danger', 'urgent': 'danger'
        };
        return priorityMap[priority?.toLowerCase()] || 'info';
    },

    getPriorityBadgeText(priority) {
        const badges = {
            'low': 'Bronze', 'medium': 'Silver', 'normal': 'Silver',
            'high': 'Gold', 'critical': 'Platinum', 'urgent': 'Platinum'
        };
        return badges[priority?.toLowerCase()] || 'Normal';
    },

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    },

    formatTimeAgo(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        if (diffMins < 1) return 'just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        const diffHours = Math.floor(diffMins / 60);
        if (diffHours < 24) return `${diffHours}h ago`;
        const diffDays = Math.floor(diffHours / 24);
        if (diffDays < 7) return `${diffDays}d ago`;
        return this.formatDate(dateString);
    },

    formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },

    truncateText(text, maxLength) {
        if (!text || text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    },

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

document.addEventListener('DOMContentLoaded', function () {
    TeamTicketsManager.init();
});