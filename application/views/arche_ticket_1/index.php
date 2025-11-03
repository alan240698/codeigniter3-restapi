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
        ?>


/* File item animations */
.file-item {
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

/* Toast animations */
@keyframes slideOut {
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

/* Drag and drop styles */
.file-upload-wrapper.dragover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.1);
    transform: scale(1.02);
}

/* Spinner animation */
.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* File List Styles with Preview */
.file-list {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.file-item:hover {
    background: #f8f9fa;
    border-color: #667eea;
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

/* File Preview */
.file-preview {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-preview-icon {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.file-preview-icon i {
    font-size: 1.75rem;
    color: white;
}

.file-info {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.file-details {
    flex: 1;
    min-width: 0;
}

.file-name {
    font-weight: 600;
    color: #2d3748;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.file-size {
    font-size: 0.8rem;
    color: #718096;
}

.file-remove {
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.file-remove:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.file-remove:active {
    transform: scale(0.95);
}

.file-remove i {
    font-size: 1rem;
}

/* Upload wrapper styles */
.file-upload-wrapper {
    border: 2px dashed #cbd5e0;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f7fafc;
    position: relative;
}

.file-upload-wrapper:hover {
    border-color: #667eea;
    background: #edf2f7;
}

.file-upload-wrapper.dragover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.1);
    transform: scale(1.02);
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
}

.file-upload-wrapper input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.upload-icon {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 1rem;
}

.upload-text {
    color: #4a5568;
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.upload-text strong {
    color: #667eea;
}

.file-limit-text {
    font-size: 0.875rem;
    color: #718096;
    margin-top: 0.5rem;
}

.file-counter {
    display: none;
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
    display: inline-block;
}

/* Spinner animation */
.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    display: inline-block;
    margin-right: 0.5rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Toast styles */
#toastContainer {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 400px;
}

.toast {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 300px;
    animation: slideIn 0.3s ease;
    color: white;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.toast.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.toast.error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.toast.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.toast.info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.toast i {
    font-size: 1.25rem;
}

.toast div {
    flex: 1;
    line-height: 1.4;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOut {
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .file-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .file-preview,
    .file-preview-icon {
        width: 100%;
        height: 120px;
    }
    
    #toastContainer {
        left: 20px;
        right: 20px;
        max-width: none;
    }
    
    .toast {
        min-width: auto;
    }
}
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- CREATE TICKET SECTION -->
        <div class="main-content">
            <?php $this->load->view('arche_ticket/partials/header_section'); ?>
            <?php $this->load->view('arche_ticket/partials/service_section', [
                'cards'    => $cards    ?? [],
                'formData' => $formData ?? []
            ]); ?>
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

    <!-- Script statictic -->
    <?php $this->load->view('arche_ticket/partials/scripts'); ?>
    <script>
const TICKET_CREATE_URL = '<?= base_url('arche_ticket/create') ?>'; // Hoặc route đúng của bạn
</script>
</body>

</html>