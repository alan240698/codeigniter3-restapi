<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - IT Ticket System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Roboto Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/it_ticket/css/user_ticket.css') ?>">
    
    <style>
        /* Original Styles */
        .ticket-gradient-header {
            background: linear-gradient(135deg, #7cb342 0%, #26c6da 100%);
            padding: 25px 30px;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        .ticket-gradient-header h4 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .view-switcher-btn {
            padding: 6px 14px;
            border: 1px solid #ddd;
            background: white;
            color: #666;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s;
        }
        .view-switcher-btn:hover:not(:disabled) {
            background: #f5f5f5;
            border-color: #26c6da;
            color: #26c6da;
        }
        .view-switcher-btn.active {
            background: #26c6da;
            color: white;
            border-color: #26c6da;
        }
        .view-switcher-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* New Modal Styles */
        #updateTicketModal .modal-content {
            border-radius: 0;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        #updateTicketModal .modal-header {
            padding: 16px 24px;
            border-bottom: 1px solid #e0e0e0;
            background-color: #fafafa;
        }
        
        .badge-bronze {
            background-color: #d4a574;
            color: white;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .ticket-id {
            color: #757575;
            font-size: 13px;
            margin-left: 10px;
            font-weight: 500;
        }
        
        .modal-main-content {
            padding: 32px 32px 24px 32px;
            background-color: white;
        }
        
        .modal-ticket-title {
            font-size: 24px;
            font-weight: 600;
            color: #212121;
            line-height: 1.3;
            flex: 1;
        }
        
        .btn-requalify-modal {
            background-color: #ff9800;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .btn-requalify-modal:hover {
            background-color: #f57c00;
        }
        
        .user-location-wrapper {
            margin-bottom: 24px;
        }
        
        .user-location-wrapper .form-select {
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 13px;
        }
        
        .modal-ticket-description {
            color: #424242;
            line-height: 1.7;
            margin-bottom: 32px;
            font-size: 14px;
        }
        
        .modal-attachments-section {
            margin-bottom: 32px;
        }
        
        .modal-attachments-title {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #212121;
        }
        
        .modal-attachment-card {
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 20px 16px;
            text-align: center;
            transition: all 0.2s;
        }
        
        .modal-attachment-card:hover {
            background-color: #f5f5f5;
        }
        
        .modal-attachment-icon {
            font-size: 36px;
            color: #e53935;
            margin-bottom: 12px;
        }
        
        .modal-attachment-name {
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #212121;
        }
        
        .modal-attachment-size {
            font-size: 12px;
            color: #757575;
        }
        
        .modal-attachment-size a {
            color: #1976d2;
            text-decoration: underline;
            margin-left: 4px;
        }
        
        .modal-tabs-section {
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 24px;
        }
        
        .modal-tabs-section .nav-tabs {
            border: none;
        }
        
        .modal-tabs-section .nav-link {
            border: none;
            color: #757575;
            padding: 12px 4px;
            margin-right: 32px;
            font-size: 13px;
            font-weight: 500;
            background: none;
            border-bottom: 3px solid transparent;
        }
        
        .modal-tabs-section .nav-link.active {
            color: #0891b2;
            border-bottom-color: #0891b2;
            background: none;
        }
        
        .modal-tabs-section .nav-link .badge {
            font-size: 10px;
            padding: 2px 6px;
            background-color: #9e9e9e;
            border-radius: 10px;
        }
        
        .modal-comment-box {
            margin-bottom: 0;
            padding: 20px;
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        
        .modal-comment-box textarea {
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 13px;
            resize: none;
            padding: 12px;
            background-color: white;
        }
        
        .modal-comment-box textarea:focus {
            border-color: #0891b2;
            box-shadow: none;
            outline: none;
        }
        
        .modal-comment-box textarea::placeholder {
            color: #bdbdbd;
        }
        
        .btn-submit-comment-modal {
            background-color: #16a34a;
            color: white;
            border: none;
            padding: 8px 24px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .btn-submit-comment-modal:hover {
            background-color: #15803d;
        }
        
        .modal-comment-item {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .modal-comment-item:last-child {
            border-bottom: none;
            margin-bottom: 24px;
        }
        
        .modal-comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        
        .modal-comment-content {
            flex: 1;
        }
        
        .modal-comment-author {
            font-size: 13px;
            font-weight: 600;
            margin-right: 8px;
        }
        
        .modal-comment-time {
            font-size: 12px;
            color: #6c757d;
        }
        
        .modal-comment-text {
            font-size: 13px;
            color: #6c757d;
            margin-top: 4px;
        }
        
        .modal-sidebar {
            background-color: #fafafa;
            padding: 32px 24px;
            border-left: 1px solid #e0e0e0;
            min-height: 600px;
        }
        
        .modal-sidebar-section {
            margin-bottom: 40px;
        }
        
        .modal-sidebar-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #212121;
        }
        
        .btn-escalate-modal {
            background-color: #fbbf24;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .modal-field-group {
            margin-bottom: 20px;
        }
        
        .modal-field-label {
            font-size: 12px;
            color: #757575;
            margin-bottom: 8px;
            font-weight: 400;
        }
        
        .modal-field-value {
            font-size: 13px;
            color: #212121;
            font-weight: 500;
        }
        
        #updateTicketModal .form-select-sm {
            font-size: 13px;
            padding: 8px 12px;
            border-radius: 4px;
            background-color: #f5f5f5;
            border: 1px solid #e0e0e0;
        }
        
        #updateTicketModal .form-select:focus {
            border-color: #0891b2;
            box-shadow: none;
            outline: none;
        }
        
        .modal-assign-link {
            font-size: 12px;
            color: #1976d2;
            text-decoration: none;
            margin-top: 6px;
            display: inline-block;
        }
        
        .modal-assign-link:hover {
            text-decoration: underline;
        }
        
        .btn-add-note-modal {
            border: 1px solid #d0d0d0;
            background: white;
            color: #757575;
            font-size: 12px;
            padding: 8px 12px;
            border-radius: 4px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .modal-note-item {
            background-color: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 12px 32px 12px 12px;
            font-size: 12px;
            margin-bottom: 10px;
            position: relative;
            color: #424242;
            line-height: 1.5;
        }
        
        .modal-note-close {
            position: absolute;
            right: 10px;
            top: 10px;
            font-size: 12px;
            opacity: 0.6;
        }
        
        .modal-note-close:hover {
            opacity: 1;
        }
        
        #updateTicketModal .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-top: 1px solid #e0e0e0;
            background-color: #fafafa;
            padding: 16px 24px;
        }
        
        .btn-add-solution-modal {
            background-color: #0891b2;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .btn-add-solution-modal:hover {
            background-color: #0e7490;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="card shadow-sm" style="border: none; border-radius: 8px; overflow: hidden;">
            <!-- Gradient Header -->
            <div class="ticket-gradient-header">
                <h4>
                    <i class="fas fa-ticket-alt"></i>
                    IT TICKET
                </h4>
            </div>
            
            <div class="card-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="my-request-tab" data-bs-toggle="tab" 
                                data-bs-target="#my-request" type="button" role="tab">
                            My Request
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="my-assigned-tab" data-bs-toggle="tab" 
                                data-bs-target="#my-assigned" type="button" role="tab">
                            My Assigned Tickets
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="my-request" role="tabpanel">
                        <!-- Search & Actions -->
                        <div class="row mb-3">
                            <div class="col-md-6 text-start">
                                <button type="button" class="btn btn-secondary me-2" id="btn-refresh">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                                <button type="button" class="btn btn-info text-white" id="btn-create-ticket">
                                    <i class="fas fa-plus"></i> Create New
                                </button>
                            </div>
                        </div>

                        <!-- Loading Overlay -->
                        <div id="table-loading" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading tickets...</p>
                        </div>

                        <!-- Empty State -->
                        <div id="table-empty" class="text-center py-5" style="display: none;">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tickets found</p>
                        </div>

                        <!-- Tickets Table -->
                        <div class="table-responsive" id="table-container">
                            <table class="table table-hover align-middle modern-table" id="tickets-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;" class="text-center">ID</th>
                                        <th style="width: 140px;">Category</th>
                                        <th style="width: 200px;">Issue Type</th>
                                        <th style="width: 250px;">Description</th>
                                        <th style="width: 150px;">Assigned To</th>
                                        <th style="width: 120px;">Status</th>
                                        <th style="width: 140px;">Date Created</th>
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" id="table-info">
                                Showing 0 to 0 of 0 entries
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <!-- Entries per page -->
                                <div class="d-flex align-items-center gap-2">
                                    <label class="mb-0">Show</label>
                                    <select class="form-select form-select-sm" id="entries-per-page" style="width: auto;">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <span>entries</span>
                                </div>

                                <!-- Pagination buttons -->
                                <nav aria-label="Table pagination">
                                    <ul class="pagination pagination-sm mb-0" id="pagination-controls">
                                        <li class="page-item disabled" id="page-prev">
                                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                                        </li>
                                        <li class="page-item disabled" id="page-next">
                                            <a class="page-link" href="#">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    
                    <!-- My Assigned Tab -->
                    <div class="tab-pane fade" id="my-assigned" role="tabpanel">
                        <!-- View Switcher -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex gap-2">
                                <button class="view-switcher-btn active" id="btn-table-view">
                                    <i class="fas fa-table"></i> Table
                                </button>
                                <button class="view-switcher-btn" id="btn-kanban-view" disabled>
                                    <i class="fas fa-th"></i> Kanban
                                </button>
                            </div>
                            <button type="button" class="btn btn-secondary" id="btn-refresh-assigned">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                        
                        <!-- Loading Overlay -->
                        <div id="assigned-loading" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading assigned tickets...</p>
                        </div>
                        
                        <!-- Empty State -->
                        <div id="assigned-empty" class="text-center py-5" style="display: none;">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No assigned tickets found</p>
                        </div>
                        
                        <!-- Assigned Tickets Table -->
                        <div class="table-responsive" id="assigned-table-container">
                            <table class="table table-hover align-middle modern-table" id="assigned-tickets-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;" class="text-center">ID</th>
                                        <th style="width: 140px;">Service</th>
                                        <th style="width: 200px;">Title</th>
                                        <th style="width: 150px;">Requester</th>
                                        <th style="width: 100px;">Priority</th>
                                        <th style="width: 120px;">Status</th>
                                        <th style="width: 140px;">Created</th>
                                        <th style="width: 100px;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="assigned-table-body">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" id="assigned-table-info">
                                Showing 0 to 0 of 0 entries
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="mb-0">Show</label>
                                    <select class="form-select form-select-sm" id="assigned-entries-per-page" style="width: auto;">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <span>entries</span>
                                </div>
                                <nav aria-label="Table pagination">
                                    <ul class="pagination pagination-sm mb-0" id="assigned-pagination-controls">
                                        <li class="page-item disabled" id="assigned-page-prev">
                                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                                        </li>
                                        <li class="page-item disabled" id="assigned-page-next">
                                            <a class="page-link" href="#">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Create Ticket -->
    <?php $this->load->view('it_ticket/user/ticket_form', [
        'service_groups' => $service_groups ?? [], 
        'ticket_types' => $ticket_types ?? [],
        'cards' => $cards ?? [], 
        'formData' => $formData ?? [], 
        'country' => $country ?? 'Vietnam'
    ]); ?>
    
    <!-- Modal: Update Ticket (NEW DESIGN) -->
    <div class="modal fade" id="updateTicketModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 1400px;">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <span class="badge-bronze" id="update-ticket-badge">Bronze</span>
                        <span class="ticket-id" id="update-ticket-number">ID-001</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="row g-0">
                    <!-- Main Content -->
                    <div class="col-lg-9">
                        <div class="modal-main-content">
                            <input type="hidden" id="update-ticket-id">
                            
                            <!-- Title and Re-qualify button -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h1 class="modal-ticket-title mb-0" id="update-ticket-title">Group website > Bug, error, permission</h1>
                                <button class="btn-requalify-modal">
                                    <i class="fas fa-redo"></i>
                                    Re-qualify
                                </button>
                            </div>
                            
                            <!-- User Location -->
                            <div class="user-location-wrapper mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-user" style="color: #212121;"></i>
                                    <span style="color: #212121; font-weight: 500;">User Location</span>
                                    <select class="form-select form-select-sm" id="update-country" style="width: auto; border-color: #d0d0d0; font-size: 13px;">
                                        <option>Viet Nam</option>
                                        <option>Thailand</option>
                                        <option>Singapore</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <p class="modal-ticket-description" id="update-ticket-description">
                                Improve the user experience and visual appeal of the bottom sheet to improve accessibility. 
                                Prioritize flexibility on multiple content-layouts.
                            </p>
                            
                            <!-- Attachments -->
                            <div class="modal-attachments-section" id="attachments-section" style="display: none;">
                                <div class="modal-attachments-title">Attachments (<span id="attachment-count">0</span>)</div>
                                <div class="row g-3" id="attachments-container">
                                    <!-- Attachments will be loaded dynamically -->
                                </div>
                            </div>
                            
                            <!-- Tabs -->
                            <div class="modal-tabs-section">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active" id="comments-tab-btn" data-bs-toggle="tab" data-bs-target="#comments-tab">
                                            <i class="far fa-comment"></i> Comments 
                                            <span class="badge" id="comments-count">2</span>
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" id="logs-tab-btn" data-bs-toggle="tab" data-bs-target="#logs-tab">
                                            <i class="far fa-clock"></i> Logs 
                                            <span class="badge" id="logs-count">6</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Tab Content -->
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="comments-tab">
                                    <!-- Comments List -->
                                    <div id="comments-list">
                                        <!-- Sample Comment -->
                                        <div class="modal-comment-item">
                                            <div class="modal-comment-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="modal-comment-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span class="modal-comment-author">Alexandar Wright</span>
                                                        <span class="modal-comment-time">• 32m ago</span>
                                                    </div>
                                                    <button class="btn btn-link p-0" style="color: #9e9e9e;">
                                                        <i class="fas fa-ellipsis-h"></i>
                                                    </button>
                                                </div>
                                                <p class="modal-comment-text">
                                                    Thanks, Omah. Once I receive the list, I'll conduct thorough research on each 
                                                    competitor's products, features, pricing, and target market.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Add Comment Box -->
                                    <div class="modal-comment-box">
                                        <textarea class="form-control" id="update-comment" rows="4" placeholder="Add a comment..."></textarea>
                                        <div class="d-flex justify-content-end mt-2">
                                            <button class="btn-submit-comment-modal" id="btn-submit-comment">Submit</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="logs-tab">
                                    <div id="logs-list">
                                        <p class="text-muted">Activity logs will appear here...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Sidebar -->
                    <div class="col-lg-3">
                        <div class="modal-sidebar">
                            <!-- Metadata Section -->
                            <div class="modal-sidebar-section">
                                <div class="modal-sidebar-title">
                                    <span>Metadata</span>
                                    <button class="btn-escalate-modal">
                                        <i class="fas fa-level-up-alt"></i>
                                        Escalate
                                    </button>
                                </div>
                                
                                <div class="modal-field-group">
                                    <div class="modal-field-label">Assigned Team</div>
                                    <select class="form-select form-select-sm" id="update-assigned-team">
                                        <option>Select Team</option>
                                        <option selected>Group IT</option>
                                        <option>Marketing</option>
                                        <option>Sales</option>
                                    </select>
                                </div>
                                
                                <div class="modal-field-group">
                                    <div class="modal-field-label">Assigned to</div>
                                    <select class="form-select form-select-sm" id="update-assigned-to">
                                        <option>Select User</option>
                                        <option selected>Luong Le Thanh</option>
                                        <option>John Doe</option>
                                    </select>
                                    <a href="#" class="modal-assign-link" id="assign-to-me">Assign to me</a>
                                </div>
                                
                                <div class="modal-field-group">
                                    <div class="modal-field-label">Observer</div>
                                    <select class="form-select form-select-sm" id="update-observer">
                                        <option>Select Observer</option>
                                        <option selected>Hien Nguyen Thi Thuc</option>
                                        <option>Alex Wright</option>
                                    </select>
                                    <a href="#" class="modal-assign-link" id="assign-observer-to-me">Add more +</a>
                                </div>
                                
                                <div class="modal-field-group">
                                    <div class="modal-field-label">Priority</div>
                                    <select class="form-select form-select-sm" id="update-priority">
                                        <option>Low</option>
                                        <option selected>Medium</option>
                                        <option>High</option>
                                        <option>Critical</option>
                                    </select>
                                </div>
                                
                                <div class="modal-field-group">
                                    <div class="modal-field-label">Status</div>
                                    <select class="form-select form-select-sm" id="update-status-display">
                                        <option selected>—</option>
                                        <option>Open</option>
                                        <option>In Progress</option>
                                        <option>Resolved</option>
                                    </select>
                                </div>
                                
                                <div class="modal-field-group">
                                    <div class="modal-field-label">Opening date</div>
                                    <div class="modal-field-value" id="update-opening-date">2025-12-25 15:38:50</div>
                                </div>
                            </div>
                            
                            <!-- Private Notes Section -->
                            <div class="modal-sidebar-section">
                                <div class="modal-sidebar-title">
                                    <span>Private Notes</span>
                                    <i class="fas fa-chevron-up" style="font-size: 12px;"></i>
                                </div>
                                
                                <button class="btn-add-note-modal mb-3" id="btn-add-note">
                                    <i class="fas fa-plus"></i> Add note
                                </button>
                                
                                <div id="private-notes-list">
                                    <div class="modal-note-item">
                                        <button class="btn-close modal-note-close" style="padding: 0; width: 16px; height: 16px;"></button>
                                        See related task/ticket for details.
                                    </div>
                                    <div class="modal-note-item">
                                        <button class="btn-close modal-note-close" style="padding: 0; width: 16px; height: 16px;"></button>
                                        This change is within the defined scope.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" style="font-size: 13px; padding: 8px 20px; border-radius: 4px;">Close</button>
                    <button class="btn btn-add-solution-modal" id="btn-update-ticket">Add Solution</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        const BASE_URL = '<?= base_url() ?>';
        const EMPLOYEE_ID = <?= $employee_id ?? 1 ?>;
        const COUNTRY = '<?= $country ?? "Vietnam" ?>';
        
        window.formDataCategories = <?= json_encode($formData ?? []) ?>;
        window.cardsData = <?= json_encode($cards ?? []) ?>;
    </script>
    
    <script src="<?= base_url('assets/it_ticket/js/file_upload.js') ?>"></script>
    <script src="<?= base_url('assets/it_ticket/js/validation.js') ?>"></script>
    <script src="<?= base_url('assets/it_ticket/js/user_ticket.js') ?>"></script>
    <script src="<?= base_url('assets/it_ticket/js/ticket_list.js') ?>"></script>
    <script src="<?= base_url('assets/it_ticket/js/team_tickets.js') ?>"></script>
</body>
</html>