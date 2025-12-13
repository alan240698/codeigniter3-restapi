/**
 * UI Handler for IT Ticket System
 */

const TicketUI = {
    /**
     * Show loading state
     */
    showLoading: function (button) {
        const $btn = $(button);
        $btn.prop('disabled', true);
        $btn.data('original-html', $btn.html());
        $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
    },

    /**
     * Hide loading state
     */
    hideLoading: function (button) {
        const $btn = $(button);
        $btn.prop('disabled', false);
        $btn.html($btn.data('original-html'));
    },

    /**
     * Show success message
     */
    showSuccess: function (message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            confirmButtonColor: '#0dcaf0',
            timer: 3000,
            timerProgressBar: true
        });
    },

    /**
     * Show error message
     */
    showError: function (message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            confirmButtonColor: '#dc3545'
        });
    },

    /**
     * Show confirmation dialog
     */
    showConfirm: function (message, callback) {
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    },

    /**
     * Show form error
     */
    showFormError: function (message) {
        const $alert = $('#form-error-alert');
        $('#form-error-message').text(message);
        $alert.removeClass('d-none');

        // Scroll to error
        $alert[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    },

    /**
     * Hide form error
     */
    hideFormError: function () {
        $('#form-error-alert').addClass('d-none');
    },

    /**
     * Update character count
     */
    updateCharCount: function (textarea) {
        const count = $(textarea).val().length;
        const maxLength = CONFIG.DESCRIPTION.MAX_LENGTH;
        $('#char-count').text(count);

        if (count > maxLength) {
            $('#char-count').addClass('text-danger');
        } else {
            $('#char-count').removeClass('text-danger');
        }
    },

    /**
     * Render file list
     */
    renderFileList: function (files) {
        const $fileList = $('#file-list');
        $fileList.empty();

        if (!files || files.length === 0) {
            return;
        }

        files.forEach((file, index) => {
            const fileSize = (file.size / 1024).toFixed(2);
            const fileItem = `
                <div class="file-item" data-index="${index}">
                    <div>
                        <i class="fas fa-file me-2"></i>
                        <span class="fw-semibold">${file.name}</span>
                        <span class="text-muted ms-2">(${fileSize} KB)</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger btn-remove-file" data-index="${index}">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
            `;
            $fileList.append(fileItem);
        });
    },

    /**
     * Render status badge
     */
    renderStatusBadge: function (status) {

        const color = CONFIG.STATUS_COLORS[status] || 'secondary';
        return `<span class="badge bg-${color}">${status}</span>`;
    },

    /**
     * Render action buttons
     */
    renderActionButtons: function (ticketId, status) {
        return `
            <button class="btn btn-sm btn-info text-white btn-view" data-id="${ticketId}" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
        `;
    },

    /**
     * Show detail loading
     */
    showDetailLoading: function () {
        $('#detail-loading').show();
        $('#detail-content').hide();
        $('#detail-error').addClass('d-none');
    },

    /**
     * Hide detail loading
     */
    hideDetailLoading: function () {
        $('#detail-loading').hide();
    },

    /**
     * Show detail content
     */
    showDetailContent: function () {
        $('#detail-content').show();
    },

    /**
     * Show detail error
     */
    showDetailError: function (message) {
        $('#detail-error-message').text(message);
        $('#detail-error').removeClass('d-none');
        $('#detail-loading').hide();
        $('#detail-content').hide();
    },

    /**
     * Populate ticket details
     */
    populateTicketDetails: function (ticket) {

        // Header information
        $('#detail-ticket-number').text('#' + ticket.id);
        $('#detail-ticket-title').text(ticket.name || 'Ticket Details');
        $('#detail-status').html(this.renderStatusBadge(ticket.status));

        // Main information
        $('#detail-category').text(ticket.status || 'N/A');
        $('#detail-type').text(ticket.type || 'N/A');
        $('#detail-created-date').text(ticket.date_creation || 'N/A');
        $('#detail-assigned').text(ticket?.supporter.length > 0 ? ticket?.supporter[0]['realname'] : 'GroupIs/GroupIT');
        $('#detail-priority').text(ticket.priority || 'Medium');
        $('#detail-updated-date').text(ticket.date_mod || 'N/A');

        // Description
        $('#detail-description').text(ticket.content || 'No description provided');

        // Attachments
        if (ticket.attachments && ticket.attachments.length > 0) {
            this.renderAttachments(ticket.attachments);
            $('#attachments-section').show();
        } else {
            $('#attachments-section').hide();
        }

        // Timeline
        if (ticket.timeline && ticket.timeline.length > 0) {
            this.renderTimeline(ticket.timeline);
        } else {
            $('#detail-timeline').html(`
                <div class="text-center text-muted py-3">
                    <i class="fas fa-history fa-2x mb-2"></i>
                    <p class="mb-0">No activity yet</p>
                </div>
            `);
        }

        // Reopen button
        if (ticket.status === 'closed' || ticket.status === 'solved') {
            $('#btn-reopen-ticket').show().data('ticket-id', ticket.id);
        } else {
            $('#btn-reopen-ticket').hide();
        }
    },

    /**
     * Render attachments
     */
    renderAttachments: function (attachments) {
        const $container = $('#detail-attachments');
        $container.empty();

        attachments.forEach(file => {
            const icon = this.getFileIcon(file.extension);
            const fileSize = file.size ? file.size : 'Unknown size';
            const item = `
                <a href="#" class="list-group-item list-group-item-action attachment-item" 
                   data-id="${file.id}">
                    <i class="fas fa-${icon} attachment-icon"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">${file.name}</div>
                        <small class="text-muted">${fileSize}</small>
                    </div>
                </a>
            `;
            $container.append(item);
        });
    },

    /**
     * Render timeline
     */
    renderTimeline: function (timeline) {
        const $container = $('#detail-timeline');
        $container.empty();

        if (!timeline || timeline.length === 0) {
            $container.html(`
                <div class="text-center text-muted py-3">
                    <i class="fas fa-history fa-2x mb-2"></i>
                    <p class="mb-0">No activity yet</p>
                </div>
            `);
            return;
        }

        timeline.forEach(item => {
            const timelineItem = `
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <strong>${item.user || 'System'}</strong>
                            <small class="text-muted">${item.date || ''}</small>
                        </div>
                        <div>${item.content || ''}</div>
                    </div>
                </div>
            `;
            $container.append(timelineItem);
        });
    },

    /**
     * Get file icon based on extension
     */
    getFileIcon: function (extension) {
        const icons = {
            'pdf': 'file-pdf',
            'doc': 'file-word',
            'docx': 'file-word',
            'xls': 'file-excel',
            'xlsx': 'file-excel',
            'jpg': 'file-image',
            'jpeg': 'file-image',
            'png': 'file-image',
            'gif': 'file-image',
            'zip': 'file-archive',
            'rar': 'file-archive',
            'txt': 'file-alt',
            'csv': 'file-csv'
        };
        return icons[extension?.toLowerCase()] || 'file';
    },

    /**
     * Reset form
     */
    resetForm: function () {
        $('#ticket-form')[0].reset();
        $('#selected-category').val('');
        $('#dynamic-form-section').hide();
        $('#subcategory').empty().append('<option value="">--Select Subcategory--</option>');
        $('#file-list').empty();
        $('.category-card').removeClass('active');
        this.hideFormError();
        this.updateCharCount($('#description'));
    },

    /**
     * Format date
     */
    formatDate: function (dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    },

    /**
     * Format datetime
     */
    formatDateTime: function (dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}-${month}-${year} ${hours}:${minutes}`;
    },

    /**
     * Truncate text
     */
    truncateText: function (text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    },

    /**
     * Show loading overlay
     */
    showLoadingOverlay: function () {
        const overlay = `
            <div id="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                 background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div class="spinner-border text-info" style="width: 3rem; height: 3rem;"></div>
            </div>
        `;
        $('body').append(overlay);
    },

    /**
     * Hide loading overlay
     */
    hideLoadingOverlay: function () {
        $('#loading-overlay').remove();
    },

    /**
     * Validate file
     */
    validateFile: function (file) {
        const errors = [];

        // Check file size
        if (file.size > CONFIG.FILE_UPLOAD.MAX_SIZE) {
            errors.push(`${file.name}: File size exceeds ${CONFIG.FILE_UPLOAD.MAX_SIZE_MB}MB`);
        }

        // Check file type
        const extension = file.name.split('.').pop().toLowerCase();
        if (!CONFIG.FILE_UPLOAD.ALLOWED_TYPES.includes(extension)) {
            errors.push(`${file.name}: File type not allowed. Allowed types: ${CONFIG.FILE_UPLOAD.ALLOWED_TYPES.join(', ')}`);
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    }
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TicketUI;
}