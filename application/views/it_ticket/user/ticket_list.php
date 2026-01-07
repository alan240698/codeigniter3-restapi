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
</head>
<body>
    <style>
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
    </style>
    
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
    
<!-- Modal: Update Ticket -->
<div class="modal fade" id="updateTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light border-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary" id="update-ticket-badge">Bronze</span>
                    <span class="text-muted" id="update-ticket-number">ID-001</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-0">
                <input type="hidden" id="update-ticket-id">
                
                <div class="row g-0">
                    <!-- Left Sidebar: Details Section -->
                    <div class="col-md-3 p-4 bg-light border-end">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold">Details</h6>
                                <button class="btn btn-sm btn-warning text-white">
                                    <i class="fas fa-redo"></i> Re-qualify
                                </button>
                            </div>
                            
                            <!-- Country -->
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Country</label>
                                <select class="form-select form-select-sm" id="update-country">
                                    <option value="Vietnam">Viet Nam</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Singapore">Singapore</option>
                                </select>
                            </div>
                            
                            <!-- Assigned Team -->
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Assigned Team</label>
                                <select class="form-select form-select-sm" id="update-assigned-team">
                                    <option value="">Select Team</option>
                                </select>
                            </div>
                            
                            <!-- Assigned to -->
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Assigned to</label>
                                <div class="position-relative">
                                    <select class="form-select form-select-sm" id="update-assigned-to">
                                        <option value="">Select User</option>
                                    </select>
                                    <a href="#" class="text-primary small d-block mt-1" id="assign-to-me">Assign to me</a>
                                </div>
                            </div>
                            
                            <!-- Observer -->
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Observer</label>
                                <div class="position-relative">
                                    <select class="form-select form-select-sm" id="update-observer">
                                        <option value="">Select Observer</option>
                                    </select>
                                    <a href="#" class="text-primary small d-block mt-1" id="assign-observer-to-me">Assign to me</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Center Content: Ticket Details -->
                    <div class="col-md-6 p-4">
                        <!-- Ticket Title -->
                        <h5 id="update-ticket-title" class="mb-3 fw-bold"></h5>
                        
                        <!-- Description -->
                        <p id="update-ticket-description" class="text-muted mb-4" style="line-height: 1.6;"></p>
                        
                        <!-- Attachments Section -->
                        <div class="mb-4" id="attachments-section" style="display: none;">
                            <h6 class="mb-3">Attachments (<span id="attachment-count">0</span>)</h6>
                            <div class="row g-3" id="attachments-container">
                                <!-- Attachments will be loaded here -->
                            </div>
                        </div>
                        
                        <!-- Comments/Logs Tabs -->
                        <ul class="nav nav-tabs border-bottom-0 mb-3" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active border-0 border-bottom border-3 border-info px-3" 
                                        id="comments-tab-btn" data-bs-toggle="tab" data-bs-target="#comments-tab" type="button">
                                    <i class="far fa-comment"></i> Comments <span class="badge bg-secondary ms-1" id="comments-count">0</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link border-0 px-3" id="logs-tab-btn" 
                                        data-bs-toggle="tab" data-bs-target="#logs-tab" type="button">
                                    <i class="far fa-clock"></i> Logs <span class="badge bg-secondary ms-1" id="logs-count">0</span>
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="comments-tab">
                                <!-- Add Comment -->
                                <div class="mb-4">
                                    <textarea class="form-control" id="update-comment" rows="3" 
                                              placeholder="Add a comment..."></textarea>
                                    <button class="btn btn-success btn-sm mt-2" id="btn-submit-comment">
                                        Submit
                                    </button>
                                </div>
                                
                                <!-- Comments List -->
                                <div id="comments-list">
                                    <!-- Sample Comment Structure -->
                                    <div class="d-flex gap-3 mb-4">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <strong class="small">Alexandar Wright</strong>
                                                <span class="text-muted small">• 32m ago</span>
                                            </div>
                                            <p class="small mb-0 text-muted">Thanks, Omah. Once I receive the list, I'll conduct thorough research on each competitor's products, features, pricing, and target market.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="logs-tab">
                                <div id="logs-list">
                                    <p class="text-muted small">Activity logs will appear here...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Sidebar: Additional Details -->
                    <div class="col-md-3 p-4 bg-light border-start">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold">Details</h6>
                                <i class="fas fa-chevron-up text-muted"></i>
                            </div>
                            
                            <!-- Priority -->
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Priority</label>
                                <select class="form-select form-select-sm" id="update-priority">
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                            
                            <!-- Status -->
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <div class="text-muted small" id="update-status-display">—</div>
                            </div>
                            
                            <!-- Opening date -->
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Opening date</label>
                                <div class="small" id="update-opening-date">—</div>
                            </div>
                        </div>
                        
                        <!-- Private Notes Section -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold">Private Notes</h6>
                                <i class="fas fa-chevron-up text-muted"></i>
                            </div>
                            
                            <button class="btn btn-sm btn-outline-secondary w-100 mb-3" id="btn-add-note">
                                <i class="fas fa-plus"></i> Add note
                            </button>
                            
                            <div id="private-notes-list">
                                <!-- Notes will be displayed here -->
                                <div class="alert alert-light small mb-2 p-2 position-relative" style="padding-right: 30px !important;">
                                    <button type="button" class="btn-close position-absolute top-0 end-0 mt-1 me-1" 
                                            style="font-size: 0.7rem;" aria-label="Close"></button>
                                    See related task/ticket for details.
                                </div>
                                <div class="alert alert-light small mb-2 p-2 position-relative" style="padding-right: 30px !important;">
                                    <button type="button" class="btn-close position-absolute top-0 end-0 mt-1 me-1" 
                                            style="font-size: 0.7rem;" aria-label="Close"></button>
                                    This change is within the defined scope.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info text-white" id="btn-update-ticket">
                    <i class="fas fa-save"></i> Update Ticket
                </button>
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
