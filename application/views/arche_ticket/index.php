<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        <?php
            include(APPPATH . 'views/arche_ticket/styles/dashboard_styles.css');
            include(APPPATH . 'views/arche_ticket/styles/tracking_styles.css');
            include(APPPATH . 'views/arche_ticket/styles/file_upload.css');
        ?>
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- CREATE TICKET SECTION -->
        <div class="main-content">
            <!-- HEADER SECTION -->
            <?php $this->load->view('arche_ticket/partials/header_section'); ?>
            <!-- SERVICE SECTION -->
            <?php
                $this->load->view('arche_ticket/partials/service_section', [
                    'cards'    => $cards    ?? [],
                    'formData' => $formData ?? []
                ]); 
            ?>
        </div>

        <!-- TICKET TRACKING SIDEBAR -->
        <aside class="stats-sidebar">
            <?php $this->load->view('arche_ticket/partials/statistics_section'); ?>
        </aside>
    </div>

    <!-- TICKETS MODAL -->
    <?php $this->load->view('arche_ticket/partials/tickets_modal'); ?>

    <!-- TOAST NOTIFICATIONS -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- SCRIPT STATICTIC -->
    <!-- <?php $this->load->view('arche_ticket/partials/scripts'); ?> -->

       <script>
        window.BASE_URL = '<?= base_url() ?>';
    </script>
<script type="module" src="<?= base_url('assets/arche_ticket/js/app.js') ?>"></script>
</body>

</html>