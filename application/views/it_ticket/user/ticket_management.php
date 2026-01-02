<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Ticket Management' ?> - IT Ticket System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Roboto Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/it_ticket/css/ticket_management.css') ?>">
</head>
<body style="font-family: 'Roboto', sans-serif; background: #f8f9fa;">
    <div class="container-fluid" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
        <div class="card shadow-sm" style="border: none; border-radius: 8px; overflow: hidden;">
            
            <!-- Gradient Header -->
            <div class="ticket-header">
                <div class="ticket-header-content">
                    <i class="fas fa-ticket-alt ticket-header-icon"></i>
                    <h1 class="ticket-header-title">IT TICKET</h1>
                </div>
            </div>
            
            <!-- Tab Navigation -->
            <div class="ticket-tabs">
                <button class="tab-item active" data-tab="created" onclick="TicketManagement.switchTab('created')">
                    My Created Tickets
                    <span class="tab-badge" id="created-count">0</span>
                </button>
                <button class="tab-item" data-tab="assigned" onclick="TicketManagement.switchTab('assigned')" 
                        <?= ($user_role ?? 'user') === 'user' ? 'style="display:none;"' : '' ?>>
                    My Assigned Ticket
                    <span class="tab-badge" id="assigned-count">0</span>
                </button>
                <button class="tab-item" data-tab="team" onclick="TicketManagement.switchTab('team')"
                        <?= ($user_role ?? 'user') === 'user' ? 'style="display:none;"' : '' ?>>
                    My Team Ticket
                    <span class="tab-badge" id="team-count">0</span>
                </button>
            </div>
            
            <!-- View Controls -->
            <div class="view-controls">
                <div class="view-switcher">
                    <button class="view-btn active" data-view="kanban" onclick="TicketManagement.switchView('kanban')">
                        <i class="fas fa-th"></i>
                        Kanban
                    </button>
                    <button class="view-btn" data-view="table" onclick="TicketManagement.switchView('table')" disabled>
                        <i class="fas fa-table"></i>
                        Table
                    </button>
                </div>
                
                <div class="view-actions">
                    <button class="view-btn" disabled>
                        <i class="fas fa-filter"></i>
                        Filter
                        <span class="tab-badge" style="background: #999;">0</span>
                    </button>
                    <button class="view-btn" disabled>
                        <i class="fas fa-sort"></i>
                        Sort
                    </button>
                </div>
            </div>
            
            <!-- Loading Overlay -->
            <div id="loading-overlay" class="loading-overlay" style="display: none; position: relative; min-height: 400px;">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p style="color: #666; margin: 0;">Loading tickets...</p>
                </div>
            </div>
            
            <!-- Kanban Board -->
            <div id="kanban-view" class="kanban-board">
                <!-- PROCESSING Column -->
                <div class="kanban-column processing">
                    <div class="column-header">
                        <div class="column-title processing">
                            <i class="fas fa-spinner"></i>
                            PROCESSING
                            <span class="column-count" id="count-processing">0</span>
                        </div>
                        <button class="column-filter">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                    <div id="column-processing" class="column-content">
                        <!-- Tickets will be loaded here -->
                    </div>
                </div>
                
                <!-- PENDING Column -->
                <div class="kanban-column pending">
                    <div class="column-header">
                        <div class="column-title pending">
                            <i class="fas fa-clock"></i>
                            PENDING
                            <span class="column-count" id="count-pending">0</span>
                        </div>
                        <button class="column-filter">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                    <div id="column-pending" class="column-content">
                        <!-- Tickets will be loaded here -->
                    </div>
                </div>
                
                <!-- SOLVED Column -->
                <div class="kanban-column solved">
                    <div class="column-header">
                        <div class="column-title solved">
                            <i class="fas fa-check-circle"></i>
                            SOLVED
                            <span class="column-count" id="count-solved">0</span>
                        </div>
                        <button class="column-filter">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                    <div id="column-solved" class="column-content">
                        <!-- Tickets will be loaded here -->
                    </div>
                </div>
                
                <!-- CLOSED Column -->
                <div class="kanban-column closed">
                    <div class="column-header">
                        <div class="column-title closed">
                            <i class="fas fa-times-circle"></i>
                            CLOSED
                            <span class="column-count" id="count-closed">0</span>
                        </div>
                        <button class="column-filter">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                    <div id="column-closed" class="column-content">
                        <!-- Tickets will be loaded here -->
                    </div>
                </div>
            </div>
            
            <!-- Table View (Hidden for now) -->
            <div id="table-view" style="display: none; padding: 30px;">
                <p class="text-center text-muted">Table view coming soon...</p>
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
        const USER_ROLE = '<?= $user_role ?? "user" ?>';
    </script>
    
    <script src="<?= base_url('assets/it_ticket/js/ticket_management.js') ?>"></script>
    
    <script>
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            TicketManagement.init();
        });
    </script>
</body>
</html>
