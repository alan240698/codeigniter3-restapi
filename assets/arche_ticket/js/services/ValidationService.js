import { CONFIG } from '../config/constants.js';
import { formatFileSize, getFileExtension } from '../utils/format.js';
import Logger from '../utils/logger.js';

class ValidationService {
    constructor() {
        // Custom validation rules storage
        this.customRules = new Map();
        
        // Common regex patterns
        this.patterns = {
            email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            phone: /^[\d\s\-\+\(\)]+$/,
            url: /^https?:\/\/.+/,
            alphanumeric: /^[a-zA-Z0-9]+$/,
            numeric: /^\d+$/,
            alpha: /^[a-zA-Z]+$/
        };

        // Error messages (Vietnamese)
        this.messages = {
            required: '{label} is required',
            email: 'Invalid email',
            phone: 'Invalid phone number',
            url: 'Invalid URL',
            minLength: 'Minimum length is {min} characters',
            maxLength: 'Maximum length is {max} characters',
            min: 'Minimum value is {min}',
            max: 'The maximum value is {max}',
            pattern: '{label} is not in the correct format',
            alphanumeric: '{label} can only contain letters and numbers',
            numeric: '{label} can only contain numbers',
            alpha: '{label} must contain only letters'
        };
    }

    /**
     * Validate single field
     * @param {HTMLElement} field - Input field element
     * @returns {Object} Validation result
     */
    validateField(field) {
        if (!field) {
            return { valid: false, message: 'Field is null' };
        }

        const name = field.name;
        const value = field.value?.trim() || '';
        const label = this._getFieldLabel(field);
        const type = field.type;

        // Check custom rules first
        if (this.customRules.has(name)) {
            const customResult = this._validateCustomRule(field, value, label);
            if (!customResult.valid) {
                return customResult;
            }
        }

        // Required validation
        if (field.hasAttribute('required') || field.required) {
            const requiredResult = this._validateRequired(field, value, label);
            if (!requiredResult.valid) {
                return requiredResult;
            }
        }

        // Skip other validations if field is empty and not required
        if (!value && !field.hasAttribute('required')) {
            return { valid: true };
        }

        // Type-specific validation
        switch (type) {
            case 'email':
                return this._validateEmail(value);
            
            case 'tel':
            case 'phone':
                return this._validatePhone(value);
            
            case 'url':
                return this._validateUrl(value);
            
            case 'number':
                return this._validateNumber(field, value);
            
            default:
                break;
        }

        // Pattern validation
        if (field.hasAttribute('pattern')) {
            const patternResult = this._validatePattern(field, value, label);
            if (!patternResult.valid) {
                return patternResult;
            }
        }

        // Length validation
        const lengthResult = this._validateLength(field, value, label);
        if (!lengthResult.valid) {
            return lengthResult;
        }

        return { valid: true };
    }

    /**
     * Validate entire form
     * @param {HTMLFormElement} form - Form element
     * @returns {Object} Validation result with errors array
     */
    validateForm(form) {
        if (!form) {
            Logger.error('validateForm: form is null');
            return { valid: false, errors: [] };
        }

        const errors = [];
        const fields = form.querySelectorAll(
            'input:not([type="hidden"]):not([type="file"]):not([type="button"]):not([type="submit"]), select, textarea'
        );

        fields.forEach(field => {
            // Skip disabled fields
            if (field.disabled) {
                return;
            }

            const result = this.validateField(field);
            if (!result.valid) {
                errors.push({ 
                    field, 
                    message: result.message,
                    name: field.name 
                });
            }
        });

        return {
            valid: errors.length === 0,
            errors
        };
    }

    /**
     * Validate file
     * @param {File} file - File object
     * @returns {Object} Validation result
     */
    validateFile(file) {
        if (!file || !(file instanceof File)) {
            return { valid: false, errors: ['Invalid file object'] };
        }

        const errors = [];

        // Size validation
        if (file.size === 0) {
            errors.push(`File "${file.name}" is empty`);
        } else if (file.size > CONFIG.FILE.MAX_SIZE) {
            errors.push(
                `File "${file.name}" exceeds maximum size of ${formatFileSize(CONFIG.FILE.MAX_SIZE)}`
            );
        }

        // Type validation
        const ext = getFileExtension(file.name).toLowerCase();
        if (!CONFIG.FILE.ALLOWED_EXTENSIONS.includes(ext)) {
            errors.push(
                `File format .${ext} is not supported. Allowed: ${CONFIG.FILE.ALLOWED_EXTENSIONS.join(', ')}`
            );
        }

        // Name length validation
        if (file.name.length > 255) {
            errors.push('File name too long (maximum 255 characters)');
        }

        // Special characters in filename
        if (/[<>:"|?*]/.test(file.name)) {
            errors.push('File name contains invalid characters');
        }

        return {
            valid: errors.length === 0,
            errors
        };
    }

    /**
     * Validate multiple files
     * @param {FileList|File[]} files - Files to validate
     * @param {number} maxFiles - Maximum number of files allowed
     * @returns {Object} Validation result
     */
    validateFiles(files, maxFiles = CONFIG.FILE.MAX_FILES) {
        const filesArray = Array.from(files);
        const errors = [];

        // Check file count
        if (filesArray.length > maxFiles) {
            errors.push(`Maximum ${maxFiles} files allowed`);
            return { valid: false, errors };
        }

        // Validate each file
        filesArray.forEach(file => {
            const result = this.validateFile(file);
            if (!result.valid) {
                errors.push(...result.errors);
            }
        });

        return {
            valid: errors.length === 0,
            errors
        };
    }

    // ========================================
    // Private Validation Methods
    // ========================================

    /**
     * Validate required field
     * @private
     */
    _validateRequired(field, value, label) {
        const type = field.type;

        // Checkbox or radio
        if (type === 'checkbox' || type === 'radio') {
            const form = field.closest('form');
            const name = field.name;
            const isChecked = form?.querySelector(`[name="${name}"]:checked`);
            
            if (!isChecked) {
                return { 
                    valid: false, 
                    message: this._formatMessage('required', { label }) 
                };
            }
        }
        // Select
        else if (field.tagName === 'SELECT') {
            if (!value || value === '' || value === '0') {
                return { 
                    valid: false, 
                    message: this._formatMessage('required', { label }) 
                };
            }
        }
        // Regular inputs
        else if (!value) {
            return { 
                valid: false, 
                message: this._formatMessage('required', { label }) 
            };
        }

        return { valid: true };
    }

    /**
     * Validate email
     * @private
     */
    _validateEmail(value) {
        if (!value) return { valid: true };

        if (!this.patterns.email.test(value)) {
            return { 
                valid: false, 
                message: this.messages.email 
            };
        }

        return { valid: true };
    }

    /**
     * Validate phone number
     * @private
     */
    _validatePhone(value) {
        if (!value) return { valid: true };

        // Remove spaces and common separators
        const cleaned = value.replace(/[\s\-\(\)]/g, '');

        if (cleaned.length < 10 || cleaned.length > 15) {
            return { 
                valid: false, 
                message: this.messages.phone 
            };
        }

        if (!this.patterns.phone.test(value)) {
            return { 
                valid: false, 
                message: this.messages.phone 
            };
        }

        return { valid: true };
    }

    /**
     * Validate URL
     * @private
     */
    _validateUrl(value) {
        if (!value) return { valid: true };

        if (!this.patterns.url.test(value)) {
            return { 
                valid: false, 
                message: this.messages.url 
            };
        }

        return { valid: true };
    }

    /**
     * Validate number input
     * @private
     */
    _validateNumber(field, value) {
        if (!value) return { valid: true };

        const numValue = parseFloat(value);

        if (isNaN(numValue)) {
            return { 
                valid: false, 
                message: 'Giá trị phải là số' 
            };
        }

        const min = field.getAttribute('min');
        const max = field.getAttribute('max');

        if (min !== null && numValue < parseFloat(min)) {
            return { 
                valid: false, 
                message: this._formatMessage('min', { min }) 
            };
        }

        if (max !== null && numValue > parseFloat(max)) {
            return { 
                valid: false, 
                message: this._formatMessage('max', { max }) 
            };
        }

        return { valid: true };
    }

    /**
     * Validate pattern
     * @private
     */
    _validatePattern(field, value, label) {
        if (!value) return { valid: true };

        const pattern = field.getAttribute('pattern');
        const regex = new RegExp(pattern);

        if (!regex.test(value)) {
            return { 
                valid: false, 
                message: this._formatMessage('pattern', { label }) 
            };
        }

        return { valid: true };
    }

    /**
     * Validate length
     * @private
     */
    _validateLength(field, value, label) {
        if (!value) return { valid: true };

        const minLength = field.getAttribute('minlength');
        const maxLength = field.getAttribute('maxlength');

        if (minLength && value.length < parseInt(minLength)) {
            return { 
                valid: false, 
                message: this._formatMessage('minLength', { label, min: minLength }) 
            };
        }

        if (maxLength && value.length > parseInt(maxLength)) {
            return { 
                valid: false, 
                message: this._formatMessage('maxLength', { label, max: maxLength }) 
            };
        }

        return { valid: true };
    }

    /**
     * Validate custom rule
     * @private
     */
    _validateCustomRule(field, value, label) {
        const rule = this.customRules.get(field.name);

        if (typeof rule === 'function') {
            try {
                const result = rule(value, field);
                
                if (result === true || result.valid === true) {
                    return { valid: true };
                }
                
                return { 
                    valid: false, 
                    message: result.message || result || 'Validation failed' 
                };
            } catch (error) {
                Logger.error('Custom validation error:', error);
                return { valid: false, message: 'Validation error occurred' };
            }
        }

        return { valid: true };
    }

    /**
     * Get field label
     * @private
     */
    _getFieldLabel(field) {
        // Try to find associated label
        const id = field.id;
        if (id) {
            const label = document.querySelector(`label[for="${id}"]`);
            if (label) {
                return label.textContent.replace(/[*:]/g, '').trim();
            }
        }

        // Try parent form-group label
        const formGroup = field.closest('.form-group, .field-group');
        const groupLabel = formGroup?.querySelector('label');
        if (groupLabel) {
            return groupLabel.textContent.replace(/[*:]/g, '').trim();
        }

        // Fallback to placeholder or name
        return field.placeholder || field.name || 'This field';
    }

    /**
     * Format message with placeholders
     * @private
     */
    _formatMessage(messageKey, params = {}) {
        let message = this.messages[messageKey] || messageKey;

        Object.keys(params).forEach(key => {
            message = message.replace(`{${key}}`, params[key]);
        });

        return message;
    }

    // ========================================
    // Public API Methods
    // ========================================

    /**
     * Add custom validation rule
     * @param {string} fieldName - Field name
     * @param {Function} validator - Validation function
     */
    addRule(fieldName, validator) {
        if (!fieldName || typeof validator !== 'function') {
            throw new Error('Invalid rule parameters');
        }

        this.customRules.set(fieldName, validator);
        Logger.debug(`Added custom rule for ${fieldName}`);
    }

    /**
     * Remove custom validation rule
     * @param {string} fieldName - Field name
     */
    removeRule(fieldName) {
        this.customRules.delete(fieldName);
        Logger.debug(`Removed custom rule for ${fieldName}`);
    }

    /**
     * Clear all custom rules
     */
    clearRules() {
        this.customRules.clear();
        Logger.debug('Cleared all custom rules');
    }

    /**
     * Set custom error message
     * @param {string} key - Message key
     * @param {string} message - Error message
     */
    setMessage(key, message) {
        this.messages[key] = message;
    }

    /**
     * Add custom pattern
     * @param {string} name - Pattern name
     * @param {RegExp} pattern - Regular expression
     */
    addPattern(name, pattern) {
        if (!(pattern instanceof RegExp)) {
            throw new Error('Pattern must be a RegExp');
        }

        this.patterns[name] = pattern;
        Logger.debug(`Added pattern: ${name}`);
    }
}

export default new ValidationService();