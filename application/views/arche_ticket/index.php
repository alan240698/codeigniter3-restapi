<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/arche_ticket/css/style.css') ?>">
    <!-- File Upload CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/arche_ticket/css/file-upload.css') ?>">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h4 class="mb-0">IT Ticket</h4>
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
                    <!-- <li class="nav-item" role="presentation">
                        <button class="nav-link" id="team-ticket-tab" data-bs-toggle="tab" 
                                data-bs-target="#team-ticket" type="button" role="tab">
                            My Team Ticket
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="follow-up-tab" data-bs-toggle="tab" 
                                data-bs-target="#follow-up" type="button" role="tab">
                            Need to Follow Up
                        </button>
                    </li> -->
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="my-request" role="tabpanel">
                        <?php $this->load->view('arche_ticket/partials/table_list'); ?>
                    </div>
                    <div class="tab-pane fade" id="team-ticket" role="tabpanel">
                        <?php $this->load->view('arche_ticket/partials/table_list'); ?>
                    </div>
                    <div class="tab-pane fade" id="follow-up" role="tabpanel">
                        <?php $this->load->view('arche_ticket/partials/table_list'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Create/Edit Ticket -->
    <?php $this->load->view('arche_ticket/partials/ticket_form', ['cards' => $cards, 'formData' => $formData, 'country' => $country]); ?>



    <!-- Scripts - NO DataTables! -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        const BASE_URL = '<?= base_url() ?>';
        const COUNTRY = '<?= $country ?>';
        
        // Pass form data to JavaScript
        window.formDataCategories = <?= json_encode($formData ?? []) ?>;
        window.cardsData = <?= json_encode($cards ?? []) ?>;
        
        console.log('Initialized with country:', COUNTRY);
        console.log('Form data available:', window.formDataCategories);
    </script>
    
    
    <script src="<?= base_url('assets/arche_ticket/js/config.js') ?>"></script>
    <script src="<?= base_url('assets/arche_ticket/js/api.js') ?>"></script>
    <script src="<?= base_url('assets/arche_ticket/js/ui.js') ?>"></script>
    <script src="<?= base_url('assets/arche_ticket/js/truncate-helper.js') ?>"></script>
    <script src="<?= base_url('assets/arche_ticket/js/table-helpers.js') ?>"></script>
    <script src="<?= base_url('assets/arche_ticket/js/main.js') ?>"></script></body>
</html>
