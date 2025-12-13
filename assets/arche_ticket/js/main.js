/**
 * OPTIMIZED VERSION - Lazy Load with Caching + FIXED FILE UPLOAD
 */

// Global variables
let allTickets = [];
let ticketDetailsCache = {}; // Cache for loaded details
let filteredTickets = [];
let selectedFiles = []; // Array to store selected files properly
let formDataCategories = {};
let loadingDetails = {}; // Track loading state

// File upload constants
const FILE_UPLOAD_CONFIG = {
    MAX_FILES: 3,
    MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB
    MAX_TOTAL_SIZE: 100 * 1024 * 1024, // 100MB
    ALLOWED_TYPES: ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt']
};

// Pagination state
let currentPage = 1;
let itemsPerPage = 10;
let sortColumn = 'created_date';
let sortOrder = 'desc';

// Filters state
let filters = {
    category: '',
    priority: '',
    status: '',
    entity: '',
    search: ''
};

$(document).ready(function () {
    console.log('Ticket app initialized - OPTIMIZED VERSION with FIXED FILE UPLOAD');
    loadFormData();
    initializeEventHandlers();
    loadTickets();
});

/**
 * Load form data from PHP
 */
function loadFormData() {
    if (typeof window.formDataCategories !== 'undefined') {
        formDataCategories = window.formDataCategories;
        console.log('Form data loaded:', formDataCategories);
    }
}

/**
 * Initialize all event handlers
 */
function initializeEventHandlers() {
    // Create New Ticket
    $('#btn-create-ticket').on('click', function () {
        TicketUI.resetForm();
        selectedFiles = []; // Reset file array
        $('#attachments').val('');
        $('#file-list').empty();
        $('#ticketFormModal').modal('show');
    });

    // Refresh button
    $('#btn-refresh').on('click', function () {
        ticketDetailsCache = {}; // Clear cache
        loadingDetails = {};
        loadTickets();
    });

    // Search input
    let searchTimeout;
    $('#search-input').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            filters.search = $('#search-input').val();
            applyFilters();
        }, 500);
    });

    // Clear search
    $('#btn-clear-search').on('click', function () {
        $('#search-input').val('');
        filters.search = '';
        applyFilters();
    });

    // Filter dropdowns
    $('#filter-category').on('change', function () {
        filters.category = $(this).val();
        applyFilters();
    });

    $('#filter-type').on('change', function () {
        filters.priority = $(this).val();
        applyFilters();
    });

    $('#filter-status').on('change', function () {
        filters.status = $(this).val();
        applyFilters();
    });

    $('#filter-assigned').on('change', function () {
        filters.entity = $(this).val();
        applyFilters();
    });

    // Entries per page
    $('#entries-per-page').on('change', function () {
        e.target.value = '';
    });

    // Remove file - FIXED HANDLER
    $(document).on('click', '.remove-file-btn', function () {
        const index = parseInt($(this).data('index'));
        removeFile(index);
    });

    // Character count
    $('#description').on('input', function () {
        TicketUI.updateCharCount(this);
    });

    // Form submit
    $('#ticket-form').on('submit', function (e) {
        e.preventDefault();
        handleFormSubmit();
    });

    // Download attachment
    $(document).on('click', '.attachment-item', function (e) {
        e.preventDefault();
        const documentId = $(this).data('id');
        TicketAPI.downloadDocument(documentId);
    });

    // Reopen ticket
    $('#btn-reopen-ticket').on('click', function () {
        const ticketId = $(this).data('ticket-id');
        handleReopenTicket(ticketId);
    });

    // Pagination
    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && page !== currentPage) {
            currentPage = page;
            renderTable();
            loadVisibleTicketDetails(); // Load details for new page
        }
    });

    $('#page-prev').on('click', function (e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            renderTable();
            loadVisibleTicketDetails();
        }
    });

    $('#page-next').on('click', function (e) {
        e.preventDefault();
        const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
            loadVisibleTicketDetails();
        }
    });
}

/**
 * FIXED: Handle file selection
 * Logic:
 * 1. If already at max (3 files) -> Reject and keep old files
 * 2. If selecting more than remaining slots -> Auto trim to fit
 * 3. Validate all files before adding
 * 4. Never lose existing files on error
 */
function handleFileSelection(files) {
    if (!files || files.length === 0) return;

    let newFiles = Array.from(files);
    console.log(`User selected ${newFiles.length} new file(s). Current files: ${selectedFiles.length}`);

    // Calculate remaining slots
    const remainingSlots = FILE_UPLOAD_CONFIG.MAX_FILES - selectedFiles.length;

    // ========================================
    // CASE 1: Already at maximum capacity
    // ========================================
    if (remainingSlots === 0) {
        showFileError(`Maximum ${FILE_UPLOAD_CONFIG.MAX_FILES} files allowed. You already have ${selectedFiles.length} file(s) selected. Please remove some files first.`);
        // CRITICAL: Keep selectedFiles unchanged - DO NOT clear
        console.log('✗ Rejected: Already at max capacity. Keeping existing files.');
        // CRITICAL: Re-render to keep UI showing existing files
        renderFileList();
        return;
    }

    // ========================================
    // CASE 2: Selected more files than available slots
    // ========================================
    if (newFiles.length > remainingSlots) {
        // Automatically trim to fit available slots
        const originalCount = newFiles.length;
        newFiles = newFiles.slice(0, remainingSlots);
        showFileError(`Maximum ${FILE_UPLOAD_CONFIG.MAX_FILES} files allowed. Only the first ${remainingSlots} file(s) will be added. You selected ${originalCount} file(s).`);
        console.log(`⚠ Warning: Trimmed from ${originalCount} to ${newFiles.length} files`);
    }

    // ========================================
    // VALIDATION PHASE - Check all files before adding
    // ========================================

    // Calculate current total size
    let currentTotalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);
    let newFilesTotalSize = 0;

    // Validate EACH file (after trimming)
    for (let file of newFiles) {
        // Check individual file size (10MB max)
        if (file.size > FILE_UPLOAD_CONFIG.MAX_FILE_SIZE) {
            showFileError(`File "${file.name}" exceeds 10MB limit (${formatFileSize(file.size)}). Please choose a smaller file.`);
            // CRITICAL: Keep selectedFiles unchanged
            console.log('✗ Rejected: File too large. Keeping existing files.');
            // CRITICAL: Re-render to keep UI showing existing files
            renderFileList();
            return;
        }

        // Check file extension
        const extension = file.name.split('.').pop().toLowerCase();
        if (!FILE_UPLOAD_CONFIG.ALLOWED_TYPES.includes(extension)) {
            showFileError(`File "${file.name}" has invalid type (.${extension}). Allowed: ${FILE_UPLOAD_CONFIG.ALLOWED_TYPES.join(', ')}`);
            // CRITICAL: Keep selectedFiles unchanged
            console.log('✗ Rejected: Invalid file type. Keeping existing files.');
            // CRITICAL: Re-render to keep UI showing existing files
            renderFileList();
            return;
        }

        // Check for duplicate file names
        if (selectedFiles.some(f => f.name === file.name)) {
            showFileError(`File "${file.name}" is already selected. Please choose a different file.`);
            // CRITICAL: Keep selectedFiles unchanged
            console.log('✗ Rejected: Duplicate file. Keeping existing files.');
            // CRITICAL: Re-render to keep UI showing existing files
            renderFileList();
            return;
        }

        // Accumulate new files size
        newFilesTotalSize += file.size;
    }

    // Check combined total size (100MB max)
    const finalTotalSize = currentTotalSize + newFilesTotalSize;
    if (finalTotalSize > FILE_UPLOAD_CONFIG.MAX_TOTAL_SIZE) {
        showFileError(`Total file size would exceed 100MB limit. Current: ${formatFileSize(currentTotalSize)}, Adding: ${formatFileSize(newFilesTotalSize)}, Total would be: ${formatFileSize(finalTotalSize)}`);

        // CRITICAL: Keep selectedFiles unchanged
        // CRITICAL: Re-render to keep UI showing existing files
        console.log('✗ Rejected: Total size too large. Keeping existing files.');
        renderFileList();
        return;
    }

    // ========================================
    // ALL VALIDATIONS PASSED - Add files
    // ========================================
    selectedFiles = [...selectedFiles, ...newFiles];
    console.log(`✓ Successfully added ${newFiles.length} file(s). Total now: ${selectedFiles.length}`);

    // Update UI
    renderFileList();
    updateFileInput();
}

/**
 * FIXED: Remove file from array
 */
function removeFile(index) {
    console.log(`Removing file at index ${index}`);

    if (index >= 0 && index < selectedFiles.length) {
        const removedFile = selectedFiles.splice(index, 1)[0];
        console.log(`Removed file: ${removedFile.name}`);

        // Update UI
        renderFileList();
        updateFileInput();

        // Hide error if shown
        hideFileError();
    }
}

/**
 * FIXED: Render file list in UI - ENHANCED VERSION
 */
function renderFileList() {
    const $fileList = $('#file-list');
    $fileList.empty();

    if (selectedFiles.length === 0) {
        return;
    }

    // Create container with modern styling
    const $container = $('<div>').addClass('uploaded-files-container');

    selectedFiles.forEach((file, index) => {
        // Get file extension for icon
        const extension = file.name.split('.').pop().toLowerCase();
        const fileIcon = getFileIcon(extension);

        // Create file item with modern design
        const $fileItem = $('<div>')
            .addClass('uploaded-file-item')
            .html(`
                <div class="file-info">
                    <div class="file-icon">
                        <i class="fas fa-${fileIcon}"></i>
                    </div>
                    <div class="file-details">
                        <div class="file-name" title="${file.name}">${file.name}</div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="file-remove-btn remove-file-btn" data-index="${index}" title="Remove file">
                    <i class="fas fa-times"></i>
                </button>
            `);

        $container.append($fileItem);
    });

    $fileList.append($container);

    // Add summary info with modern design
    const totalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);
    const $summary = $('<div>')
        .addClass('files-summary')
        .html(`
            <div class="summary-item">
                <i class="fas fa-paperclip me-1"></i>
                <span><strong>${selectedFiles.length}</strong> / ${FILE_UPLOAD_CONFIG.MAX_FILES} files</span>
            </div>
            <div class="summary-item">
                <i class="fas fa-hdd me-1"></i>
                <span><strong>${formatFileSize(totalSize)}</strong> / 100MB</span>
            </div>
        `);

    $fileList.append($summary);
}

/**
 * Get file icon based on extension
 */
function getFileIcon(extension) {
    const iconMap = {
        'pdf': 'file-pdf',
        'doc': 'file-word',
        'docx': 'file-word',
        'xls': 'file-excel',
        'xlsx': 'file-excel',
        'ppt': 'file-powerpoint',
        'pptx': 'file-powerpoint',
        'jpg': 'file-image',
        'jpeg': 'file-image',
        'png': 'file-image',
        'gif': 'file-image',
        'txt': 'file-alt',
        'zip': 'file-archive',
        'rar': 'file-archive'
    };
    return iconMap[extension] || 'file';
}

/**
 * Update file input with current selected files
 */
function updateFileInput() {
    // Create a new DataTransfer object to update the file input
    const dataTransfer = new DataTransfer();

    selectedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });

    // Update the file input
    const fileInput = document.getElementById('attachments');
    if (fileInput) {
        fileInput.files = dataTransfer.files;
    }
}

/**
 * Format file size for display
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Show file error message
 */
function showFileError(message) {
    const $errorAlert = $('#form-error-alert');
    const $errorMessage = $('#form-error-message');

    $errorMessage.text(message);
    $errorAlert.removeClass('d-none');

    // Scroll to error
    $errorAlert[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Auto hide after 5 seconds
    setTimeout(() => {
        $errorAlert.addClass('d-none');
    }, 5000);
}

/**
 * Hide file error message
 */
function hideFileError() {
    $('#form-error-alert').addClass('d-none');
}

/**
 * Load tickets - ONLY LIST DATA
 */
async function loadTickets() {
    console.log('Loading tickets list...');
    showLoading();

    try {
        const response = await TicketAPI.getList();
        console.log('API Response:', response);

        if (response.success && response.data) {
            allTickets = response.data;
            filteredTickets = [...allTickets];

            populateFilters();
            sortTickets();
            renderTable();

            // Load details for visible tickets
            loadVisibleTicketDetails();
        } else {
            showEmpty();
        }
    } catch (error) {
        console.error('Error loading tickets:', error);
        TicketUI.showError(CONFIG.MESSAGES.ERROR.LOAD);
        showEmpty();
    } finally {
        hideLoading();
    }
}

/**
 * Load details for VISIBLE tickets only
 */
async function loadVisibleTicketDetails() {
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const visibleTickets = filteredTickets.slice(start, end);

    console.log(`Loading details for ${visibleTickets.length} visible tickets`);

    for (const ticket of visibleTickets) {
        // Skip if already cached or loading
        if (ticketDetailsCache[ticket.id] || loadingDetails[ticket.id]) {
            continue;
        }

        // Load in background
        loadTicketDetailById(ticket.id);
    }
}

/**
 * Load single ticket detail
 */
async function loadTicketDetailById(ticketId) {
    // Check cache
    if (ticketDetailsCache[ticketId]) {
        console.log(`Using cached detail for ticket ${ticketId}`);
        return ticketDetailsCache[ticketId];
    }

    // Check if already loading
    if (loadingDetails[ticketId]) {
        console.log(`Already loading detail for ticket ${ticketId}`);
        return;
    }

    loadingDetails[ticketId] = true;

    try {
        const response = await TicketAPI.getById(ticketId);

        if (response.success && response.data) {
            // Cache the result
            ticketDetailsCache[ticketId] = response.data;
            console.log(`Cached detail for ticket ${ticketId}`);

            // Update the row in table
            updateTableRowWithDetail(ticketId, response.data);

            return response.data;
        }
    } catch (error) {
        console.error(`Error loading detail for ticket ${ticketId}:`, error);
    } finally {
        delete loadingDetails[ticketId];
    }

    return null;
}

/**
 * Update table row with loaded detail
 */
function updateTableRowWithDetail(ticketId, detail) {
    const $row = $(`tr[data-ticket-id="${ticketId}"]`);

    if ($row.length === 0) return;

    // Update description
    $row.find('td:eq(2)').html(detail.content || 'No description');

    // Update supporter
    const supporter = detail.supporter && detail.supporter.length > 0
        ? detail.supporter[0]['realname']
        : 'GroupIS/GroupIT';
    $row.find('td:eq(3)').text(supporter);

    // Update dates
    $row.find('td:eq(6)').text(detail.date_creation || 'N/A');
    $row.find('td:eq(7)').text(detail.date_mod || 'N/A');

    // Remove loading class
    $row.removeClass('loading');

    console.log(`Updated row for ticket ${ticketId}`);
}

/**
 * Populate filter dropdowns
 */
function populateFilters() {
    console.log('Populating filters from allTickets:', allTickets);

    const categories = [...new Set(window.cardsData.map(t => t.key).filter(Boolean))];
    const priorities = [...new Set(allTickets.map(t => t.priority).filter(Boolean))];
    const statuses = [...new Set(allTickets.map(t => t.status).filter(Boolean))];
    const entities = [...new Set(allTickets.map(t => t.entity).filter(Boolean))];

    // Populate category filter
    const $category = $('#filter-category');
    $category.find('option:not(:first)').remove();
    categories.forEach(cat => {
        $category.append(`<option value="${cat}">${cat}</option>`);
    });

    // Populate priority filter
    const $type = $('#filter-type');
    $type.find('option:not(:first)').remove();
    priorities.forEach(priority => {
        $type.append(`<option value="${priority}">${priority}</option>`);
    });

    // Populate status filter
    const $status = $('#filter-status');
    $status.find('option:not(:first)').remove();
    statuses.forEach(status => {
        $status.append(`<option value="${status}">${status}</option>`);
    });

    // Populate assigned filter
    const $assigned = $('#filter-assigned');
    $assigned.find('option:not(:first)').remove();
    entities.forEach(entity => {
        $assigned.append(`<option value="${entity}">${entity}</option>`);
    });
}

/**
 * Apply filters
 */
function applyFilters() {
    console.log('Applying filters:', filters);

    filteredTickets = allTickets.filter(ticket => {
        // Category filter
        if (filters.category && ticket.category !== filters.category) {
            return false;
        }

        // Priority filter
        if (filters.priority && ticket.priority !== filters.priority) {
            return false;
        }

        // Status filter
        if (filters.status && filters.status !== '') {
            const ticketStatus = String(ticket.status || '').trim().toLowerCase();
            const filterStatus = String(filters.status).trim().toLowerCase();

            if (ticketStatus !== filterStatus) {
                return false;
            }
        }

        // Entity filter
        if (filters.entity && ticket.entity !== filters.entity) {
            return false;
        }

        // Search filter
        if (filters.search) {
            const searchLower = filters.search.toLowerCase();
            const detail = ticketDetailsCache[ticket.id];

            const searchableText = [
                ticket.title,
                ticket.category,
                ticket.priority,
                ticket.description,
                ticket.status,
                ticket.entity,
                detail?.content || ''
            ].join(' ').toLowerCase();

            if (!searchableText.includes(searchLower)) {
                return false;
            }
        }

        return true;
    });

    console.log('Filtered tickets count:', filteredTickets.length);

    currentPage = 1;
    sortTickets();
    renderTable();
    loadVisibleTicketDetails();
}

/**
 * Sort tickets
 */
function sortTickets() {
    filteredTickets.sort((a, b) => {
        let aVal = a[sortColumn];
        let bVal = b[sortColumn];

        if (!aVal) aVal = '';
        if (!bVal) bVal = '';

        aVal = String(aVal).toLowerCase();
        bVal = String(bVal).toLowerCase();

        if (sortOrder === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });
}

/**
 * Update sort icons
 */
function updateSortIcons() {
    $('.sortable .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');

    const $activeColumn = $(`.sortable[data-sort="${sortColumn}"] .sort-icon`);
    $activeColumn.removeClass('fa-sort');
    $activeColumn.addClass(sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
}

/**
 * Render table - Show placeholder if no detail yet
 */
function renderTable() {
    const $tbody = $('#table-body');
    $tbody.empty();

    if (filteredTickets.length === 0) {
        showEmpty();
        return;
    }

    hideEmpty();

    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageTickets = filteredTickets.slice(start, end);

    pageTickets.forEach(ticket => {
        const row = createTableRow(ticket);
        $tbody.append(row);
    });

    updateTableInfo();
    updatePagination();
}

/**
 * Create table row - Use cached detail or show loading
 */
function createTableRow(ticket) {
    const detail = ticketDetailsCache[ticket.id];
    const isLoading = !detail && loadingDetails[ticket.id];

    const description = detail ? (detail.content || 'No description') :
        (isLoading ? '<i class="fas fa-spinner fa-spin"></i> Loading...' :
            '<span class="text-muted">Loading...</span>');

    // Supporter/assigned rendering without background
    let supporterHtml = '';
    if (detail && detail.supporter && detail.supporter.length > 0) {
        const supporterName = detail.supporter[0]['realname'];
        supporterHtml = `<i class="fas fa-user-check" style="color: #667eea; margin-right: 6px;"></i>${supporterName}`;
    } else if (isLoading) {
        supporterHtml = '<i class="fas fa-spinner fa-spin"></i>';
    } else {
        supporterHtml = `<i class="fas fa-users" style="color: #718096; margin-right: 6px;"></i>GroupIS/GroupIT`;
    }

    const dateCreation = detail ? (detail.date_creation || 'N/A') :
        (isLoading ? '<i class="fas fa-spinner fa-spin"></i>' : 'N/A');

    // Enhanced category rendering with badge
    const categoryHtml = ticket.category ?
        `<span class="category-badge"><i class="fas fa-tag me-1"></i>${ticket.category}</span>` :
        'N/A';

    // Status rendering without background
    const statusHtml = renderStatusWithoutBackground(ticket.status);

    const rowClass = isLoading ? 'loading' : '';

    return `
        <tr data-ticket-id="${ticket.id}" class="${rowClass}">
            <td class="text-center">${ticket.id}</td>
            <td><strong>${ticket.title || ticket.number || 'N/A'}</strong></td>
            <td>${description}</td>
            <td>${supporterHtml}</td>
            <td>${categoryHtml}</td>
            <td>${statusHtml}</td>
            <td>${dateCreation}</td>
        </tr>
    `;
}

/**
 * Render status without background - plain text with icon
 */
function renderStatusWithoutBackground(status) {
    if (!status) return '<i class="fas fa-question" style="color: #718096; margin-right: 6px;"></i>Unknown';

    const statusLower = status.toLowerCase();
    const statusConfig = {
        'processing': { icon: 'fa-spinner fa-spin', color: '#ed8936' },
        'new': { icon: 'fa-star', color: '#667eea' },
        'assigned': { icon: 'fa-user-check', color: '#4299e1' },
        'pending': { icon: 'fa-clock', color: '#ed8936' },
        'solved': { icon: 'fa-check-circle', color: '#48bb78' },
        'closed': { icon: 'fa-lock', color: '#718096' },
        'rejected': { icon: 'fa-times-circle', color: '#f56565' }
    };

    const config = statusConfig[statusLower] || { icon: 'fa-info-circle', color: '#718096' };

    return `<i class="fas ${config.icon}" style="color: ${config.color}; margin-right: 6px;"></i>${status}`;
}

/**
 * Update table info text
 */
function updateTableInfo() {
    const start = (currentPage - 1) * itemsPerPage + 1;
    const end = Math.min(start + itemsPerPage - 1, filteredTickets.length);
    const total = filteredTickets.length;

    $('#table-info').text(`Showing ${start} to ${end} of ${total} entries`);
}

/**
 * Update pagination controls
 */
function updatePagination() {
    const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
    const $pagination = $('#pagination-controls');

    $pagination.find('.page-number').remove();

    for (let i = 1; i <= totalPages; i++) {
        const isActive = i === currentPage;
        const pageItem = `
            <li class="page-item page-number ${isActive ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `;
        $pagination.find('#page-next').before(pageItem);
    }

    $('#page-prev').toggleClass('disabled', currentPage === 1);
    $('#page-next').toggleClass('disabled', currentPage === totalPages);
}

/**
 * Show/Hide states
 */
function showLoading() {
    $('#table-loading').show();
    $('#table-container').hide();
    $('#table-empty').hide();
}

function hideLoading() {
    $('#table-loading').hide();
    $('#table-container').show();
}

function showEmpty() {
    $('#table-container').hide();
    $('#table-loading').hide();
    $('#table-empty').show();
}

function hideEmpty() {
    $('#table-empty').hide();
    $('#table-container').show();
}

/**
 * View ticket detail
 */
async function viewTicketDetail(ticketId) {
    $('#ticketDetailModal').modal('show');
    TicketUI.showDetailLoading();

    try {
        // Check cache first
        let detail = ticketDetailsCache[ticketId];

        if (!detail) {
            // Load from API
            const response = await TicketAPI.getById(ticketId);
            if (response.success && response.data) {
                detail = response.data;
                ticketDetailsCache[ticketId] = detail;
            }
        }

        if (detail) {
            TicketUI.populateTicketDetails(detail);
            TicketUI.hideDetailLoading();
            TicketUI.showDetailContent();
        } else {
            TicketUI.showDetailError(CONFIG.MESSAGES.ERROR.LOAD);
        }
    } catch (error) {
        console.error('Error loading ticket detail:', error);
        TicketUI.showDetailError(CONFIG.MESSAGES.ERROR.LOAD);
    }
}

/**
 * Load subcategories
 */
function loadSubcategories(category) {
    console.log('Loading subcategories for:', category);

    const $subcategory = $('#subcategory');
    $subcategory.empty().append('<option value="">--Select Subcategory--</option>');

    if (formDataCategories[category]) {
        const categoryData = formDataCategories[category];

        if (categoryData.categories && Array.isArray(categoryData.categories)) {
            categoryData.categories.forEach(cat => {
                $subcategory.append(`<option value="${cat.id}">${cat.name}</option>`);
            });

            if (categoryData.categories.length > 0) {
                $('#subcategory-section').show();
            } else {
                $('#subcategory-section').hide();
            }
        }
    }
}

/**
 * Validate form
 */
function validateForm() {
    hideFileError();

    const category = $('#selected-category').val();
    const subcategory = $('#subcategory').val();
    const description = $('#description').val().trim();

    if (!category) {
        showFileError('Please select a category');
        return false;
    }

    if (!subcategory) {
        showFileError('Please select an issue type');
        return false;
    }

    if (!description || description.length < 10) {
        showFileError('Please enter a description (minimum 10 characters)');
        return false;
    }

    if (description.length > 5000) {
        showFileError('Description is too long (maximum 5000 characters)');
        return false;
    }

    return true;
}

/**
 * Handle form submit
 */
async function handleFormSubmit() {
    if (!validateForm()) return;

    const $submitBtn = $('#btn-submit-final');
    $submitBtn.prop('disabled', true);
    $submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Submitting...');

    try {
        const formData = new FormData();
        formData.append('category', $('#selected-category').val());
        formData.append('subcategory', $('#subcategory').val());
        formData.append('description', $('#description').val());
        formData.append('country_glpi', $('#country-glpi').val());

        // Append all selected files
        selectedFiles.forEach((file, index) => {
            formData.append('attachments[]', file);
            console.log(`Appending file ${index + 1}: ${file.name} (${formatFileSize(file.size)})`);
        });

        const response = await TicketAPI.create(formData);

        if (response.success) {
            alert('Ticket created successfully!');
            $('#ticketFormModal').modal('hide');

            // Reset form and files
            TicketUI.resetForm();
            selectedFiles = [];
            $('#attachments').val('');
            $('#file-list').empty();
            hideFileError();

            // Clear cache and reload
            ticketDetailsCache = {};
            loadTickets();
        } else {
            showFileError(response.message || 'Failed to create ticket. Please try again.');
        }
    } catch (error) {
        console.error('Form submission error:', error);
        showFileError('An error occurred while submitting your ticket. Please try again.');
    } finally {
        $submitBtn.prop('disabled', false);
        $submitBtn.html('Submit Ticket <i class="fas fa-paper-plane ms-2"></i>');
    }
}

/**
 * Handle reopen ticket
 */
function handleReopenTicket(ticketId) {
    if (!confirm('Are you sure you want to reopen this ticket?')) {
        return;
    }

    TicketAPI.reopen(ticketId)
        .then(response => {
            if (response.success) {
                alert('Ticket reopened successfully!');
                $('#ticketDetailModal').modal('hide');

                // Clear cache for this ticket
                delete ticketDetailsCache[ticketId];

                loadTickets();
            } else {
                alert(response.message || 'Failed to reopen ticket');
            }
        })
        .catch(error => {
            console.error('Error reopening ticket:', error);
            alert('An error occurred while reopening the ticket');
        });
}