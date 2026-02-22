/**
 * File Upload with Drag & Drop and Paste Support
 * FINAL VERSION - Perfect UI/UX
 */

// Store selected files as plain array
let selectedFiles = [];

// File upload configuration
const FILE_UPLOAD_CONFIG = {
    MAX_FILES: 3,
    MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB per file
    MAX_TOTAL_SIZE: 30 * 1024 * 1024, // 30MB total
    ALLOWED_EXTENSIONS: ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt']
};

/**
 * Initialize drag & drop and paste functionality
 */
function initializeFileUpload() {
    const uploadZone = document.getElementById('file-upload-zone');
    const fileInput = document.getElementById('attachments');

    if (!uploadZone || !fileInput) {
        console.warn('File upload elements not found');
        return;
    }

    // Click to browse
    uploadZone.addEventListener('click', (e) => {
        if (e.target.id !== 'attachments' && !e.target.closest('label[for="attachments"]')) {
            fileInput.click();
        }
    });

    // Drag & Drop events
    uploadZone.addEventListener('dragover', handleDragOver);
    uploadZone.addEventListener('dragleave', handleDragLeave);
    uploadZone.addEventListener('drop', handleDrop);

    // File input change
    fileInput.addEventListener('change', function (e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            handleFileSelection(files);
        }
    });

    // Paste event
    document.addEventListener('paste', handlePaste);

    console.log('✅ File upload initialized');
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');

    const files = e.dataTransfer.files;
    if (files && files.length > 0) {
        handleFileSelection(files);
    }
}

function handlePaste(e) {
    const modal = document.getElementById('ticketFormModal');
    if (!modal || !modal.classList.contains('show')) return;

    if (e.target.tagName === 'TEXTAREA' ||
        e.target.tagName === 'INPUT' ||
        e.target.isContentEditable) {
        return;
    }

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
        handleFileSelection(files);
    }
}

function handleFileSelection(files) {
    if (!files || files.length === 0) return;

    let newFiles = Array.from(files);
    hideFileError();

    const backupFiles = [...selectedFiles];
    const remainingSlots = FILE_UPLOAD_CONFIG.MAX_FILES - selectedFiles.length;

    if (remainingSlots === 0) {
        showFileError(
            `You can only upload up to ${FILE_UPLOAD_CONFIG.MAX_FILES} files. ` +
            `Please remove some files first.`
        );
        updateFileInput();
        return;
    }

    let filesRejectedByCount = 0;
    if (newFiles.length > remainingSlots) {
        filesRejectedByCount = newFiles.length - remainingSlots;
        newFiles = newFiles.slice(0, remainingSlots);
    }

    const validationResult = validateAllNewFiles(newFiles);

    if (validationResult.validFiles.length > 0) {
        selectedFiles = [...backupFiles, ...validationResult.validFiles];
    }

    renderFileList();
    updateFileInput();

    const allErrors = [...validationResult.errors];
    if (filesRejectedByCount > 0) {
        allErrors.unshift(
            `${filesRejectedByCount} file(s) rejected (maximum ${FILE_UPLOAD_CONFIG.MAX_FILES} files allowed)`
        );
    }

    if (allErrors.length > 0) {
        showFileError(allErrors.join(' | '));
    } else if (validationResult.validFiles.length > 0) {
        showFileSuccess(`${validationResult.validFiles.length} file(s) added successfully.`);
    }
}

function validateAllNewFiles(newFiles) {
    const validFiles = [];
    const errors = [];

    const currentTotalSize = calculateTotalFileSize(selectedFiles);
    let tempTotalSize = currentTotalSize;

    for (const file of newFiles) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!FILE_UPLOAD_CONFIG.ALLOWED_EXTENSIONS.includes(ext)) {
            errors.push(`"${file.name}": unsupported format`);
            continue;
        }

        if (file.size > FILE_UPLOAD_CONFIG.MAX_FILE_SIZE) {
            errors.push(`"${file.name}": exceeds ${formatFileSize(FILE_UPLOAD_CONFIG.MAX_FILE_SIZE)}`);
            continue;
        }

        const exists = selectedFiles.find(f => f.name === file.name && f.size === file.size);
        if (exists) {
            errors.push(`"${file.name}": already added`);
            continue;
        }

        if (tempTotalSize + file.size > FILE_UPLOAD_CONFIG.MAX_TOTAL_SIZE) {
            errors.push(`"${file.name}": would exceed ${formatFileSize(FILE_UPLOAD_CONFIG.MAX_TOTAL_SIZE)} total`);
            continue;
        }

        validFiles.push(file);
        tempTotalSize += file.size;
    }

    return { isValid: validFiles.length > 0, validFiles, errors };
}

function calculateTotalFileSize(files) {
    return files.reduce((total, file) => total + file.size, 0);
}

function removeFile(index) {
    if (index < 0 || index >= selectedFiles.length) return;

    const removedFile = selectedFiles[index];
    selectedFiles.splice(index, 1);

    renderFileList();
    updateFileInput();
    hideFileError();

    // Show toast notification
    showFileInfo(`Removed: ${removedFile.name}`);
}

function updateFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));

    const fileInput = document.getElementById('attachments');
    if (fileInput) {
        fileInput.files = dataTransfer.files;
    }
}

/**
 * Render file list UI - IMPROVED VERSION
 */
function renderFileList() {
    const fileList = document.getElementById('file-list');
    if (!fileList) return;

    fileList.innerHTML = '';

    if (selectedFiles.length === 0) return;

    const listContainer = document.createElement('div');
    listContainer.className = 'uploaded-files-container';

    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'uploaded-file-item';

        // Icon based on file type
        const icon = getFileIcon(file.name);
        const iconColor = getIconColor(file.name);

        fileItem.innerHTML = `
            <div class="file-icon ${iconColor}">
                <i class="fas fa-${icon}"></i>
            </div>
            <div class="file-details">
                <div class="file-name" title="${file.name}">${truncateFileName(file.name, 35)}</div>
                <div class="file-meta">
                    <span class="file-size">${formatFileSize(file.size)}</span>
                    <span class="file-status">
                        <i class="fas fa-check-circle text-success"></i>
                        Ready
                    </span>
                </div>
            </div>
            <button type="button" 
                    class="file-remove-btn" 
                    title="Remove ${file.name}"
                    onclick="removeFile(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;

        listContainer.appendChild(fileItem);
    });

    // Summary bar
    const summary = createFilesSummary();
    listContainer.appendChild(summary);

    fileList.appendChild(listContainer);
}

function truncateFileName(name, maxLength) {
    if (name.length <= maxLength) return name;

    const ext = name.split('.').pop();
    const nameWithoutExt = name.substring(0, name.length - ext.length - 1);
    const truncated = nameWithoutExt.substring(0, maxLength - ext.length - 4) + '...';

    return truncated + '.' + ext;
}

function getIconColor(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const colorMap = {
        'jpg': 'icon-image',
        'jpeg': 'icon-image',
        'png': 'icon-image',
        'gif': 'icon-image',
        'pdf': 'icon-pdf',
        'doc': 'icon-word',
        'docx': 'icon-word',
        'xls': 'icon-excel',
        'xlsx': 'icon-excel',
        'txt': 'icon-text'
    };
    return colorMap[ext] || 'icon-default';
}

function createFilesSummary() {
    const totalSize = calculateTotalFileSize(selectedFiles);
    const totalSizePercent = Math.round((totalSize / FILE_UPLOAD_CONFIG.MAX_TOTAL_SIZE) * 100);

    const summary = document.createElement('div');
    summary.className = 'files-summary';
    summary.innerHTML = `
        <div class="summary-stats">
            <div class="stat-item">
                <i class="fas fa-paperclip"></i>
                <span class="stat-label">Files:</span>
                <span class="stat-value">${selectedFiles.length} / ${FILE_UPLOAD_CONFIG.MAX_FILES}</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-database"></i>
                <span class="stat-label">Size:</span>
                <span class="stat-value">${formatFileSize(totalSize)} / ${formatFileSize(FILE_UPLOAD_CONFIG.MAX_TOTAL_SIZE)}</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-chart-pie"></i>
                <span class="stat-label">Usage:</span>
                <span class="stat-value">${totalSizePercent}%</span>
            </div>
        </div>
        <div class="summary-progress">
            <div class="progress-bar ${totalSizePercent > 90 ? 'progress-warning' : ''}" 
                 style="width: ${totalSizePercent}%"></div>
        </div>
    `;
    return summary;
}

function showFileError(message) {
    const errorDiv = document.getElementById('attachments-error');
    if (errorDiv) {
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        errorDiv.classList.add('show');
        errorDiv.style.display = 'block';
        errorDiv.style.color = '#dc3545';
        setTimeout(() => hideFileError(), 7000);
    }
}

function showFileSuccess(message) {
    const errorDiv = document.getElementById('attachments-error');
    if (errorDiv) {
        errorDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        errorDiv.classList.add('show');
        errorDiv.style.display = 'block';
        errorDiv.style.color = '#28a745';
        setTimeout(() => hideFileError(), 3000);
    }
}

function showFileInfo(message) {
    const errorDiv = document.getElementById('attachments-error');
    if (errorDiv) {
        errorDiv.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
        errorDiv.classList.add('show');
        errorDiv.style.display = 'block';
        errorDiv.style.color = '#17a2b8';
        setTimeout(() => hideFileError(), 2000);
    }
}

function hideFileError() {
    const errorDiv = document.getElementById('attachments-error');
    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.classList.remove('show');
        errorDiv.style.display = 'none';
    }
}

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

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function clearAllFiles() {
    selectedFiles = [];
    const fileInput = document.getElementById('attachments');
    if (fileInput) {
        fileInput.value = '';
        fileInput.files = new DataTransfer().files;
    }
    renderFileList();
    hideFileError();
}

// Make removeFile available globally
window.removeFile = removeFile;

// Initialize
document.addEventListener('DOMContentLoaded', initializeFileUpload);