/**
 * Ticket List - Main JavaScript
 * Calls existing super admin API endpoints
 */

let currentPage = 1;
let perPage = 10;
let totalRecords = 0;

// Initialize on page load
$(document).ready(function () {
    loadTickets();
    initializeEventHandlers();
});

function initializeEventHandlers() {
    // Setup real-time validation
    setupRealtimeValidation();

    // Refresh button
    $('#btn-refresh').click(function () {
        loadTickets();
    });

    // Create new ticket button
    $('#btn-create-ticket').click(function () {
        openCreateTicketModal();
    });

    // Entries per page change
    $('#entries-per-page').change(function () {
        perPage = parseInt($(this).val());
        currentPage = 1;
        loadTickets();
    });

    // Form submission
    $('#ticket-form').submit(function (e) {
        e.preventDefault();
        handleFormSubmit();
    });

    // Review button
    $('#btn-review-ticket').click(function () {
        if (validateCompleteForm()) {
            window.showReview();
        }
    });

    // File upload
    $('#attachments').change(function () {
        displayFileList(this.files);
    });
}

function loadTickets() {
    showLoading();

    const params = {
        page: currentPage,
        per_page: perPage
    };

    // Call existing super admin API
    $.ajax({
        url: BASE_URL + 'api/tickets',
        method: 'GET',
        data: params,
        success: function (response) {
            hideLoading();
            if (response.success) {
                totalRecords = response.total || 0;
                renderTickets(response.data || []);
                renderPagination(response.total_pages || 1);
                updateTableInfo();
            } else {
                showError('Failed to load tickets');
            }
        },
        error: function () {
            hideLoading();
            showError('Failed to load tickets');
        }
    });
}

function renderTickets(tickets) {
    const tbody = $('#table-body');
    tbody.empty();

    if (tickets.length === 0) {
        $('#table-container').hide();
        $('#table-empty').show();
        return;
    }

    $('#table-empty').hide();
    $('#table-container').show();

    tickets.forEach(ticket => {
        const row = `
            <tr onclick="viewTicket(${ticket.id})" style="cursor: pointer;">
                <td class="text-center">${ticket.id}</td>
                <td>${escapeHtml(ticket.category || '-')}</td>
                <td>${escapeHtml(ticket.subcategory || '-')}</td>
                <td>${truncateText(ticket.description || '-', 50)}</td>
                <td>${escapeHtml(ticket.assigned_to || 'Unassigned')}</td>
                <td><span class="badge bg-${getStatusColor(ticket.status)}">${ticket.status || 'New'}</span></td>
                <td>${formatDate(ticket.created_at)}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

function renderPagination(totalPages) {
    const pagination = $('#pagination-controls');
    pagination.find('li:not(#page-prev):not(#page-next)').remove();

    // Previous button
    if (currentPage > 1) {
        $('#page-prev').removeClass('disabled');
        $('#page-prev a').attr('onclick', `changePage(${currentPage - 1})`);
    } else {
        $('#page-prev').addClass('disabled');
        $('#page-prev a').removeAttr('onclick');
    }

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            const pageItem = `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
            $('#page-next').before(pageItem);
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            $('#page-next').before('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }
    }

    // Next button
    if (currentPage < totalPages) {
        $('#page-next').removeClass('disabled');
        $('#page-next a').attr('onclick', `changePage(${currentPage + 1})`);
    } else {
        $('#page-next').addClass('disabled');
        $('#page-next a').removeAttr('onclick');
    }
}

function changePage(page) {
    currentPage = page;
    loadTickets();
}

function updateTableInfo() {
    const start = (currentPage - 1) * perPage + 1;
    const end = Math.min(currentPage * perPage, totalRecords);
    $('#table-info').text(`Showing ${start} to ${end} of ${totalRecords} entries`);
}

function openCreateTicketModal() {
    // Reset form
    $('#ticket-form')[0].reset();
    $('#file-list').empty();
    $('#form-error-alert').addClass('d-none');
    $('#review-section').hide();
    $('#form-fields').show();
    $('#review-button-container').show();
    $('#steps-indicator').show();

    // Show modal
    const modal = new bootstrap.Modal($('#ticketFormModal')[0]);
    modal.show();
}

// Validation function is now in validation.js as validateCompleteForm()

function showFormError(message) {
    $('#form-error-message').html(message);
    $('#form-error-alert').removeClass('d-none');
    $('#form-error-alert')[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function handleFormSubmit() {
    if ($('#review-section').is(':visible')) {
        submitTicket();
    } else {
        if (validateForm()) {
            window.showReview();
        }
    }
}

function submitTicket() {
    const formData = new FormData($('#ticket-form')[0]);

    $('#btn-submit-final').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Submitting...');

    // Call existing super admin API for ticket creation
    $.ajax({
        url: BASE_URL + 'tickets/create',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Ticket created successfully',
                    confirmButtonColor: '#17a2b8'
                }).then(() => {
                    bootstrap.Modal.getInstance($('#ticketFormModal')[0]).hide();
                    loadTickets();
                });
            } else {
                showFormError(response.message || 'Failed to create ticket');
                $('#btn-submit-final').prop('disabled', false).html('Submit Ticket <i class="fas fa-paper-plane ms-2"></i>');
            }
        },
        error: function () {
            showFormError('An error occurred while submitting your ticket');
            $('#btn-submit-final').prop('disabled', false).html('Submit Ticket <i class="fas fa-paper-plane ms-2"></i>');
        }
    });
}

function displayFileList(files) {
    const fileList = $('#file-list');
    fileList.empty();

    if (files.length > 0) {
        const ul = $('<ul class="list-group"></ul>');
        Array.from(files).forEach(file => {
            const li = `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file me-2 text-muted"></i>${escapeHtml(file.name)}</span>
                    <span class="badge bg-info rounded-pill">${formatFileSize(file.size)}</span>
                </li>
            `;
            ul.append(li);
        });
        fileList.append(ul);
    }
}

function viewTicket(ticketId) {
    window.location.href = BASE_URL + 'my-tickets/' + ticketId;
}

function showLoading() {
    $('#table-loading').show();
    $('#table-container').hide();
    $('#table-empty').hide();
}

function hideLoading() {
    $('#table-loading').hide();
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#17a2b8'
    });
}

function getStatusColor(status) {
    const colors = {
        'new': 'primary',
        'assigned': 'info',
        'processing': 'warning',
        'solved': 'success',
        'closed': 'secondary'
    };
    return colors[status?.toLowerCase()] || 'secondary';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function truncateText(text, maxLength) {
    if (!text || text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
