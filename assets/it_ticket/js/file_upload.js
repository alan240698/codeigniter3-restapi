/**
 * File Upload with Drag & Drop and Paste Support
 */

// Store selected files
let selectedFiles = new DataTransfer();

/**
 * Initialize drag & drop and paste functionality
 */
function initializeFileUpload() {
    const uploadZone = document.getElementById('file-upload-zone');
    const fileInput = document.getElementById('attachments');

    if (!uploadZone || !fileInput) return;

    // Click to browse
    uploadZone.addEventListener('click', (e) => {
        if (e.target.id !== 'attachments') {
            fileInput.click();
        }
    });

    // Drag & Drop events
    uploadZone.addEventListener('dragover', handleDragOver);
    uploadZone.addEventListener('dragleave', handleDragLeave);
    uploadZone.addEventListener('drop', handleDrop);

    // File input change
    fileInput.addEventListener('change', handleFileSelect);

    // Paste event (document-wide when modal is open)
    document.addEventListener('paste', handlePaste);
}

/**
 * Handle drag over
 */
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.add('drag-over');
}

/**
 * Handle drag leave
 */
function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');
}

/**
 * Handle drop
 */
function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        addFilesToInput(files);
    }
}

/**
 * Handle file select from input
 */
function handleFileSelect(e) {
    const files = e.target.files;
    if (files.length > 0) {
        addFilesToInput(files);
    }
}

/**
 * Handle paste event
 */
function handlePaste(e) {
    // Only handle paste when modal is open
    const modal = document.getElementById('createTicketModal');
    if (!modal || !modal.classList.contains('show')) return;

    // Don't handle paste in textarea
    if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') return;

    const items = e.clipboardData.items;
    const files = [];

    for (let i = 0; i < items.length; i++) {
        if (items[i].kind === 'file') {
            const file = items[i].getAsFile();
            if (file) {
                files.push(file);
            }
        }
    }

    if (files.length > 0) {
        e.preventDefault();
        addFilesToInput(files);
    }
}

/**
 * Add files to input
 */
function addFilesToInput(newFiles) {
    const fileInput = document.getElementById('attachments');
    const errorDiv = document.getElementById('attachments-error');

    // Clear previous errors
    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.classList.remove('show');
    }

    // Convert FileList to Array
    const filesArray = Array.from(newFiles);

    // Validation constants
    const MAX_FILES = 3;
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB per file
    const MAX_TOTAL_SIZE = 30 * 1024 * 1024; // 30MB total
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];

    // Validate file count
    const totalFiles = selectedFiles.files.length + filesArray.length;
    if (totalFiles > MAX_FILES) {
        showFileError(`You can only upload up to ${MAX_FILES} files. Please remove some files first.`);
        return;
    }

    // Validate each file
    for (const file of filesArray) {
        // Check file extension
        const ext = file.name.split('.').pop().toLowerCase();
        if (!ALLOWED_EXTENSIONS.includes(ext)) {
            showFileError(`File "${file.name}" has an unsupported format. Allowed: ${ALLOWED_EXTENSIONS.join(', ').toUpperCase()}`);
            return;
        }

        // Check individual file size
        if (file.size > MAX_FILE_SIZE) {
            showFileError(`File "${file.name}" is too large (${formatFileSize(file.size)}). Maximum size per file is ${formatFileSize(MAX_FILE_SIZE)}.`);
            return;
        }

        // Check for duplicates
        let exists = false;
        for (let i = 0; i < selectedFiles.files.length; i++) {
            if (selectedFiles.files[i].name === file.name &&
                selectedFiles.files[i].size === file.size) {
                exists = true;
                break;
            }
        }

        if (exists) {
            showFileError(`File "${file.name}" is already added.`);
            return;
        }
    }

    // Calculate total size
    let totalSize = 0;
    for (let i = 0; i < selectedFiles.files.length; i++) {
        totalSize += selectedFiles.files[i].size;
    }
    for (const file of filesArray) {
        totalSize += file.size;
    }

    if (totalSize > MAX_TOTAL_SIZE) {
        showFileError(`Total file size (${formatFileSize(totalSize)}) exceeds the maximum limit of ${formatFileSize(MAX_TOTAL_SIZE)}.`);
        return;
    }

    // All validations passed, add files
    for (const file of filesArray) {
        selectedFiles.items.add(file);
    }

    // Update file input
    fileInput.files = selectedFiles.files;

    // Trigger change event for validation
    const event = new Event('change', { bubbles: true });
    fileInput.dispatchEvent(event);

    // Display files
    displayFileList(selectedFiles.files);
}

/**
 * Show file error message
 */
function showFileError(message) {
    const errorDiv = document.getElementById('attachments-error');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.add('show');

        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorDiv.classList.remove('show');
        }, 5000);
    }
}

/**
 * Display file list
 */
function displayFileList(files) {
    const fileList = document.getElementById('file-list');
    if (!fileList) return;

    fileList.innerHTML = '';

    if (files.length === 0) {
        return;
    }

    const listGroup = document.createElement('div');
    listGroup.className = 'list-group';

    Array.from(files).forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex justify-content-between align-items-center';

        const fileInfo = document.createElement('div');
        fileInfo.className = 'd-flex align-items-center flex-grow-1';

        const icon = getFileIcon(file.name);
        const iconElement = document.createElement('i');
        iconElement.className = `${icon} me-2`;

        const nameSpan = document.createElement('span');
        nameSpan.className = 'text-truncate';
        nameSpan.style.maxWidth = '300px';
        nameSpan.textContent = file.name;

        const sizeSpan = document.createElement('small');
        sizeSpan.className = 'text-muted ms-2';
        sizeSpan.textContent = `(${formatFileSize(file.size)})`;

        fileInfo.appendChild(iconElement);
        fileInfo.appendChild(nameSpan);
        fileInfo.appendChild(sizeSpan);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger';
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.onclick = () => removeFile(index);

        item.appendChild(fileInfo);
        item.appendChild(removeBtn);
        listGroup.appendChild(item);
    });

    fileList.appendChild(listGroup);
}

/**
 * Remove file from list
 */
function removeFile(index) {
    const newDataTransfer = new DataTransfer();

    for (let i = 0; i < selectedFiles.files.length; i++) {
        if (i !== index) {
            newDataTransfer.items.add(selectedFiles.files[i]);
        }
    }

    selectedFiles = newDataTransfer;

    const fileInput = document.getElementById('attachments');
    fileInput.files = selectedFiles.files;

    // Trigger change event for validation
    const event = new Event('change', { bubbles: true });
    fileInput.dispatchEvent(event);

    displayFileList(selectedFiles.files);
}

/**
 * Get file icon based on extension
 */
function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const iconMap = {
        'jpg': 'far fa-file-image text-primary',
        'jpeg': 'far fa-file-image text-primary',
        'png': 'far fa-file-image text-primary',
        'gif': 'far fa-file-image text-primary',
        'pdf': 'far fa-file-pdf text-danger',
        'doc': 'far fa-file-word text-primary',
        'docx': 'far fa-file-word text-primary',
        'xls': 'far fa-file-excel text-success',
        'xlsx': 'far fa-file-excel text-success',
        'txt': 'far fa-file-alt text-secondary'
    };

    return iconMap[ext] || 'far fa-file text-muted';
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Clear all files
 */
function clearAllFiles() {
    selectedFiles = new DataTransfer();
    const fileInput = document.getElementById('attachments');
    if (fileInput) {
        fileInput.files = selectedFiles.files;
        displayFileList(selectedFiles.files);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeFileUpload);
