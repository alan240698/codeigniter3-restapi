/**
 * Form Validation Helpers for IT Ticket System
 * Real-time validation with inline error messages
 */

// ============================================
// VALIDATION CONFIGURATION
// ============================================
const VALIDATION_CONFIG = {
    DESCRIPTION: {
        MIN_LENGTH: 10,
        MAX_LENGTH: 1000
    },
    FILE_UPLOAD: {
        MAX_FILES: 3,
        MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB
        MAX_TOTAL_SIZE: 30 * 1024 * 1024, // 30MB
        ALLOWED_TYPES: ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt']
    }
};

// ============================================
// ERROR DISPLAY FUNCTIONS
// ============================================

/**
 * Show inline error for a field
 * @param {string} fieldId - ID of the field
 * @param {string} message - Error message to display
 */
function showFieldError(fieldId, message) {
    const $field = $(`#${fieldId}`);
    const $errorDiv = $(`#${fieldId}-error`);

    // Add error class to field
    $field.addClass('error');

    // Show error message
    if ($errorDiv.length) {
        $errorDiv.html(`<i class="fas fa-exclamation-circle"></i> ${message}`);
        $errorDiv.show();
    }
}

/**
 * Clear error for a field
 * @param {string} fieldId - ID of the field
 */
function clearFieldError(fieldId) {
    const $field = $(`#${fieldId}`);
    const $errorDiv = $(`#${fieldId}-error`);

    $field.removeClass('error');
    $errorDiv.hide().empty();
}

/**
 * Clear all form errors
 */
function clearAllErrors() {
    $('.form-select, .form-control, .file-upload-box').removeClass('error');
    $('.inline-error').hide().empty();
}

/**
 * Scroll to first error field
 * @param {string} fieldId - ID of the field to scroll to
 */
function scrollToError(fieldId) {
    const $field = $(`#${fieldId}`);
    if ($field.length) {
        $field[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// ============================================
// FIELD VALIDATORS
// ============================================

/**
 * Validate service group field
 * @returns {Object} { isValid: boolean, fieldId: string, error: string }
 */
function validateServiceGroup() {
    const value = $('#service-group').val();
    return {
        fieldId: 'service-group',
        isValid: !!value,
        error: 'Please select a service group'
    };
}

/**
 * Validate IT service field
 * @returns {Object} { isValid: boolean, fieldId: string, error: string }
 */
function validateItService() {
    const value = $('#it-service').val();
    return {
        fieldId: 'it-service',
        isValid: !!value,
        error: 'Please select an IT service'
    };
}

/**
 * Validate sub-service field (only if visible)
 * @returns {Object} { isValid: boolean, fieldId: string, error: string }
 */
function validateSubService() {
    const $section = $('#sub-service-section');
    const value = $('#sub-service').val();

    if ($section.is(':visible') && !value) {
        return {
            fieldId: 'sub-service',
            isValid: false,
            error: 'Please select a sub service'
        };
    }

    return { isValid: true };
}

/**
 * Validate description field
 * @param {boolean} showError - Whether to show error message
 * @returns {Object} { isValid: boolean, fieldId: string, error: string }
 */
function validateDescription(showError = true) {
    const value = $('#description').val().trim();
    const fieldId = 'description';

    if (!value) {
        return {
            fieldId,
            isValid: false,
            error: 'Please enter a description'
        };
    }

    if (value.length < VALIDATION_CONFIG.DESCRIPTION.MIN_LENGTH) {
        return {
            fieldId,
            isValid: false,
            error: `Description must be at least ${VALIDATION_CONFIG.DESCRIPTION.MIN_LENGTH} characters`
        };
    }

    if (value.length > VALIDATION_CONFIG.DESCRIPTION.MAX_LENGTH) {
        return {
            fieldId,
            isValid: false,
            error: `Description must be less than ${VALIDATION_CONFIG.DESCRIPTION.MAX_LENGTH} characters`
        };
    }

    return { isValid: true };
}

// ============================================
// FILE VALIDATION FUNCTIONS
// ============================================

/**
 * Validate file size
 * @param {File} file - File object
 * @returns {Object} { isValid: boolean, error: string }
 */
function validateFileSize(file) {
    if (file.size > VALIDATION_CONFIG.FILE_UPLOAD.MAX_FILE_SIZE) {
        return {
            isValid: false,
            error: `File "${file.name}" exceeds 10MB limit (${formatFileSize(file.size)})`
        };
    }
    return { isValid: true };
}

/**
 * Validate file extension
 * @param {File} file - File object
 * @returns {Object} { isValid: boolean, error: string }
 */
function validateFileExtension(file) {
    const extension = file.name.split('.').pop().toLowerCase();

    if (!VALIDATION_CONFIG.FILE_UPLOAD.ALLOWED_TYPES.includes(extension)) {
        return {
            isValid: false,
            error: `File "${file.name}" has invalid type (.${extension})`
        };
    }

    return { isValid: true };
}

/**
 * Validate file duplicate
 * @param {File} file - File to check
 * @param {Array<File>} existingFiles - Already selected files
 * @returns {Object} { isValid: boolean, error: string }
 */
function validateFileDuplicate(file, existingFiles) {
    if (existingFiles.some(f => f.name === file.name)) {
        return {
            isValid: false,
            error: `File "${file.name}" is already selected`
        };
    }
    return { isValid: true };
}

/**
 * Validate total file size
 * @param {number} currentSize - Current total size
 * @param {number} newSize - New files size
 * @returns {Object} { isValid: boolean, error: string }
 */
function validateTotalSize(currentSize, newSize) {
    const totalSize = currentSize + newSize;

    if (totalSize > VALIDATION_CONFIG.FILE_UPLOAD.MAX_TOTAL_SIZE) {
        return {
            isValid: false,
            error: `Total file size would exceed 30MB limit (Current: ${formatFileSize(currentSize)}, Adding: ${formatFileSize(newSize)})`
        };
    }

    return { isValid: true };
}

/**
 * Calculate total file size
 * @param {Array<File>} files - Array of files
 * @returns {number} Total size in bytes
 */
function calculateTotalFileSize(files) {
    return files.reduce((sum, file) => sum + file.size, 0);
}

/**
 * Format file size to human readable
 * @param {number} bytes - Size in bytes
 * @returns {string} Formatted size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

// ============================================
// COMPLETE FORM VALIDATION
// ============================================

/**
 * Validate entire form
 * @returns {boolean} True if form is valid
 */
function validateCompleteForm() {
    clearAllErrors();

    const validations = [
        validateServiceGroup(),
        validateItService(),
        validateSubService(),
        validateDescription()
    ];

    let isValid = true;
    let firstErrorField = null;

    validations.forEach(result => {
        if (!result.isValid) {
            showFieldError(result.fieldId, result.error);
            if (!firstErrorField) {
                firstErrorField = result.fieldId;
            }
            isValid = false;
        }
    });

    if (!isValid && firstErrorField) {
        scrollToError(firstErrorField);
    }

    return isValid;
}

// ============================================
// REAL-TIME VALIDATION SETUP
// ============================================

/**
 * Setup real-time validation for all fields
 */
function setupRealtimeValidation() {
    // Service Group validation
    $('#service-group').on('change', function () {
        if ($(this).val()) {
            clearFieldError('service-group');
        }
    });

    // IT Service validation
    $('#it-service').on('change', function () {
        if ($(this).val()) {
            clearFieldError('it-service');
        }
    });

    // Sub Service validation
    $('#sub-service').on('change', function () {
        if ($(this).val()) {
            clearFieldError('sub-service');
        }
    });

    // Description real-time validation
    $('#description').on('input', function () {
        const value = $(this).val().trim();
        const $field = $(this);
        const $errorDiv = $('#description-error');

        // Clear error class first
        $field.removeClass('error');

        if (value.length === 0) {
            // Empty - hide error
            $errorDiv.hide();
        } else if (value.length < VALIDATION_CONFIG.DESCRIPTION.MIN_LENGTH) {
            // Too short
            $field.addClass('error');
            $errorDiv.html(`<i class="fas fa-exclamation-circle"></i> Description must be at least ${VALIDATION_CONFIG.DESCRIPTION.MIN_LENGTH} characters (${value.length}/${VALIDATION_CONFIG.DESCRIPTION.MIN_LENGTH})`);
            $errorDiv.show();
        } else if (value.length > VALIDATION_CONFIG.DESCRIPTION.MAX_LENGTH) {
            // Too long
            $field.addClass('error');
            $errorDiv.html(`<i class="fas fa-exclamation-circle"></i> Description is too long (${value.length}/${VALIDATION_CONFIG.DESCRIPTION.MAX_LENGTH})`);
            $errorDiv.show();
        } else {
            // Valid
            $errorDiv.hide();
        }
    });
}

// ============================================
// EXPORT FOR USE IN OTHER FILES
// ============================================
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        VALIDATION_CONFIG,
        showFieldError,
        clearFieldError,
        clearAllErrors,
        scrollToError,
        validateServiceGroup,
        validateItService,
        validateSubService,
        validateDescription,
        validateFileSize,
        validateFileExtension,
        validateFileDuplicate,
        validateTotalSize,
        calculateTotalFileSize,
        formatFileSize,
        validateCompleteForm,
        setupRealtimeValidation
    };
}
