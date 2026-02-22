<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - IT Ticket System</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/it_ticket/css/user_ticket.css'); ?>">
    
    <style>
        .ticket-detail-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--spacing-xl);
        }
        
        .ticket-info-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-md);
        }
        
        .ticket-header {
            border-bottom: 2px solid var(--gray-lighter);
            padding-bottom: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .ticket-number {
            font-size: 2rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 1rem;
            color: var(--dark);
            font-weight: 500;
        }
        
        .description-section {
            background: var(--off-white);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--gray-lighter);
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: var(--spacing-lg);
        }
        
        .timeline-marker {
            position: absolute;
            left: -1.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid var(--white);
            box-shadow: 0 0 0 2px var(--primary-color);
        }
        
        .timeline-content {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: var(--spacing-md);
            box-shadow: var(--shadow-sm);
        }
        
        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-sm);
        }
        
        .timeline-action {
            font-weight: 600;
            color: var(--dark);
        }
        
        .timeline-date {
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        @media (max-width: 768px) {
            .ticket-detail-container {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="user-ticket-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-ticket-alt"></i> Ticket Details</h1>
            <a href="<?php echo base_url('my-tickets'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Tickets
            </a>
        </div>

        <!-- Ticket Detail Container -->
        <div class="ticket-detail-container">
            <!-- Main Content -->
            <div>
                <!-- Ticket Info Card -->
                <div class="ticket-info-card">
                    <div class="ticket-header">
                        <div class="ticket-number" id="ticketNumber">Loading...</div>
                        <div style="margin-top: var(--spacing-sm);">
                            <span class="badge" id="ticketStatus"></span>
                            <span class="badge" id="ticketPriority" style="margin-left: var(--spacing-sm);"></span>
                        </div>
                    </div>
                    
                    <h2 id="ticketSubject" style="margin-bottom: var(--spacing-lg); color: var(--dark);">Loading...</h2>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Service Group</div>
                            <div class="info-value" id="serviceGroup">-</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Ticket Type</div>
                            <div class="info-value" id="ticketType">-</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">IT Service</div>
                            <div class="info-value" id="itService">-</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Assigned To</div>
                            <div class="info-value" id="assignedTo">-</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Created Date</div>
                            <div class="info-value" id="createdDate">-</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">SLA Due Date</div>
                            <div class="info-value" id="slaDueDate">-</div>
                        </div>
                    </div>
                    
                    <div class="description-section">
                        <h3 style="margin-bottom: var(--spacing-md); color: var(--dark);">
                            <i class="fas fa-align-left"></i> Description
                        </h3>
                        <p id="ticketDescription" style="white-space: pre-wrap; line-height: 1.6;">Loading...</p>
                    </div>
                    
                    <div id="customFieldsSection" style="display: none;">
                        <h3 style="margin-bottom: var(--spacing-md); color: var(--dark);">
                            <i class="fas fa-sliders-h"></i> Additional Information
                        </h3>
                        <div class="info-grid" id="customFieldsContent"></div>
                    </div>
                    
                    <div id="actionsSection" style="margin-top: var(--spacing-xl); padding-top: var(--spacing-xl); border-top: 2px solid var(--gray-lighter);">
                        <!-- Action buttons will be added here based on ticket status -->
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div>
                <!-- Timeline Card -->
                <div class="ticket-info-card">
                    <h3 style="margin-bottom: var(--spacing-lg); color: var(--dark);">
                        <i class="fas fa-history"></i> Activity Timeline
                    </h3>
                    
                    <div class="timeline" id="timeline">
                        <!-- Timeline items will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay hidden" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- JavaScript -->
    <script src="<?php echo base_url('assets/it_ticket/js/user_ticket.js'); ?>"></script>
    <script>
        const ticketId = <?php echo $ticket_id; ?>;
        const employeeId = <?php echo $employee_id; ?>;
        const apiBaseUrl = '<?php echo base_url(); ?>';
        
        let ticketData = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadTicketDetails();
        });

        function loadTicketDetails() {
            showLoading();
            
            fetch(`${apiBaseUrl}api/tickets/show/${ticketId}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        ticketData = data.data.ticket;
                        renderTicketDetails(ticketData);
                        renderTimeline(data.data.history);
                        renderActions(ticketData);
                    } else {
                        showToast('Failed to load ticket details', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showToast('Failed to load ticket details', 'error');
                });
        }

        function renderTicketDetails(ticket) {
            document.getElementById('ticketNumber').textContent = `#${ticket.ticket_number || ticket.id}`;
            document.getElementById('ticketSubject').textContent = ticket.subject;
            document.getElementById('ticketDescription').textContent = ticket.description;
            
            // Status badge
            const statusBadge = document.getElementById('ticketStatus');
            statusBadge.textContent = ticket.status.toUpperCase();
            statusBadge.className = `badge badge-${ticket.status}`;
            
            // Priority badge
            const priorityBadge = document.getElementById('ticketPriority');
            priorityBadge.textContent = ticket.priority.toUpperCase();
            priorityBadge.className = `badge badge-${ticket.priority}`;
            
            // Info fields
            document.getElementById('serviceGroup').textContent = ticket.service_group_name || '-';
            document.getElementById('ticketType').textContent = ticket.ticket_type_name || '-';
            document.getElementById('itService').textContent = ticket.it_service_name || '-';
            document.getElementById('assignedTo').textContent = ticket.assigned_to_name || 'Unassigned';
            document.getElementById('createdDate').textContent = formatDateTime(ticket.created_at);
            document.getElementById('slaDueDate').textContent = ticket.sla_due_date ? formatDateTime(ticket.sla_due_date) : 'N/A';
            
            // Custom fields
            if (ticket.custom_fields_data) {
                try {
                    const customFields = JSON.parse(ticket.custom_fields_data);
                    if (Object.keys(customFields).length > 0) {
                        renderCustomFields(customFields);
                    }
                } catch (e) {
                    console.error('Error parsing custom fields:', e);
                }
            }
        }

        function renderCustomFields(customFields) {
            const section = document.getElementById('customFieldsSection');
            const content = document.getElementById('customFieldsContent');
            
            content.innerHTML = '';
            
            Object.entries(customFields).forEach(([key, value]) => {
                const item = document.createElement('div');
                item.className = 'info-item';
                item.innerHTML = `
                    <div class="info-label">${key}</div>
                    <div class="info-value">${value}</div>
                `;
                content.appendChild(item);
            });
            
            section.style.display = 'block';
        }

        function renderTimeline(history) {
            const timeline = document.getElementById('timeline');
            
            if (!history || history.length === 0) {
                timeline.innerHTML = '<p style="color: var(--gray);">No activity yet</p>';
                return;
            }
            
            timeline.innerHTML = history.map(item => `
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <div class="timeline-action">
                                <i class="fas fa-${getActionIcon(item.action_type)}"></i>
                                ${formatAction(item.action_type)}
                            </div>
                            <div class="timeline-date">${formatDateTime(item.created_at)}</div>
                        </div>
                        ${item.comment ? `<p style="margin: 0; color: var(--gray);">${item.comment}</p>` : ''}
                        ${item.employee_name ? `<p style="margin-top: var(--spacing-xs); font-size: 0.85rem; color: var(--gray-light);">by ${item.employee_name}</p>` : ''}
                    </div>
                </div>
            `).join('');
        }

        function renderActions(ticket) {
            const actionsSection = document.getElementById('actionsSection');
            let actions = '';
            
            if (ticket.status === 'closed' && ticket.requester_id == employeeId) {
                actions = `
                    <button class="btn btn-primary" onclick="reopenTicket()">
                        <i class="fas fa-redo"></i> Reopen Ticket
                    </button>
                `;
            }
            
            actionsSection.innerHTML = actions;
        }

        function reopenTicket() {
            if (!confirm('Are you sure you want to reopen this ticket?')) {
                return;
            }
            
            showLoading();
            
            fetch(`${apiBaseUrl}tickets/reopen/${ticketId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    showToast('Ticket reopened successfully', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message || 'Failed to reopen ticket', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showToast('Failed to reopen ticket', 'error');
            });
        }

        function getActionIcon(actionType) {
            const icons = {
                'created': 'plus-circle',
                'assigned': 'user-check',
                'status_changed': 'exchange-alt',
                'resolved': 'check-circle',
                'closed': 'times-circle',
                'reopened': 'redo',
                'comment': 'comment'
            };
            return icons[actionType] || 'circle';
        }

        function formatAction(actionType) {
            const actions = {
                'created': 'Ticket Created',
                'assigned': 'Ticket Assigned',
                'status_changed': 'Status Changed',
                'resolved': 'Ticket Resolved',
                'closed': 'Ticket Closed',
                'reopened': 'Ticket Reopened',
                'comment': 'Comment Added'
            };
            return actions[actionType] || actionType;
        }

        function formatDateTime(dateString) {
            if (!dateString) return '-';
            
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
</body>
</html>
