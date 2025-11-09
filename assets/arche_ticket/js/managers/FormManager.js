import ApiService from '../services/ApiService.js';
import ValidationService from '../services/ValidationService.js';
import FileManager from './FileManager.js';
import StorageService from '../services/StorageService.js';
import Toast from '../components/Toast.js';
import EventBus from '../core/EventBus.js';
import Logger from '../utils/logger.js';
import { $$, addClass, removeClass } from '../utils/dom.js';

class FormManager {
    constructor() {
        this.validationListeners = new WeakMap(); // Track validation listeners
        this.isSubmitting = new Map(); // Track submission state per form
        this.currentActiveForm = null; // Track active form
        
        this._initFormSwitching();
    }

    init() {
        this._bindEvents();
    }

    destroy() {
        // Cleanup would require storing form references
        // Consider implementing if needed for SPA
    }

    /**
     * Initialize form switching behavior
     * @private
     */
    _initFormSwitching() {
        EventBus.on('form:switch', ({ fromFormId, toFormId }) => {
            Logger.info('Form switching', { fromFormId, toFormId });
            
            // Reset previous form when switching
            if (fromFormId && fromFormId !== toFormId) {
                const previousForm = document.querySelector(`form[data-category="${fromFormId}"]`);
                if (previousForm) {
                    this._resetFormOnSwitch(previousForm, fromFormId);
                }
            }
            
            this.currentActiveForm = toFormId;
        });

        // Listen to card collapse/expand events
        EventBus.on('card:expanded', ({ category }) => {
            if (this.currentActiveForm && this.currentActiveForm !== category) {
                const previousForm = document.querySelector(`form[data-category="${this.currentActiveForm}"]`);
                if (previousForm) {
                    this._resetFormOnSwitch(previousForm, this.currentActiveForm);
                }
            }
            this.currentActiveForm = category;
        });
    }

    /**
     * Reset form when switching to another form
     * @private
     */
    _resetFormOnSwitch(form, category) {
        // Reset form fields
        form.reset();
        
        // Clear errors
        this._clearFormErrors(form);
        
        // Clear file list UI
        const fileList = form.querySelector('.file-list');
        if (fileList) {
            fileList.innerHTML = '';
        }
        
        // Clear stored files
        FileManager.clearFiles(category);
        
        // Reset file input
        const fileInput = form.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.value = '';
        }
        
        Logger.info('Form reset on switch', { category });
    }

    /**
     * Submit form with validation and file upload
     * @param {HTMLFormElement} form - Form element to submit
     * @returns {Promise<void>}
     */
    async submit(form) {
        const category = form.dataset.category;
        
        // Prevent double submission
        if (this.isSubmitting.get(category)) {
            Logger.warn('Form already submitting', { category });
            return;
        }

        const btn = form.querySelector('.submit-btn');
        const btnText = btn?.querySelector('span');
        
        if (!btn || !btnText) {
            Logger.error('Submit button or text not found');
            Toast.error('Form configuration error');
            return;
        }

        const originalText = btnText.textContent;

        // Clear previous errors
        this._clearFormErrors(form);

        // Validate form
        const validation = ValidationService.validateForm(form);
        if (!validation.valid) {
            this._handleValidationErrors(form, validation.errors);
            return;
        }

        // Mark as submitting
        this.isSubmitting.set(category, true);

        // Disable button
        btn.disabled = true;
        btnText.textContent = 'Sending...';
        btn.innerHTML = `<div class="spinner"></div><span>${btnText.textContent}</span>`;

        try {
            const formData = this._collectFormData(form, category);
            const result = await ApiService.createTicket(formData);

            if (result.success) {
                this._handleSuccess(form, category, result, btn, originalText);
            } else {
                this._handleServerErrors(form, result);
            }
        } catch (error) {
            Logger.error('Form submit error:', error);
            const errorMsg = error.message || 'An error occurred. Please try again!';
            Toast.error(errorMsg);
        } finally {
            // Reset button state
            this.isSubmitting.set(category, false);
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-paper-plane"></i><span>${originalText}</span>`;
        }
    }

    /**
     * Handle successful form submission
     * @private
     */
    _handleSuccess(form, category, result, btn, originalText) {
        const ticketId = result.ticket_id || 'N/A';
        Toast.success(`Ticket #${ticketId} created successfully!`);

        this._resetForm(form, category);
        
        EventBus.emit('ticket:created', result);
        
        // Collapse card after delay
        setTimeout(() => {
            EventBus.emit('card:collapse');
        }, 1500);
    }

    /**
     * Handle validation errors
     * @private
     */
    _handleValidationErrors(form, errors) {
        errors.forEach(({ field, message }) => {
            this._showFieldError(field, message);
        });

        Toast.error(errors[0].message);
        
        // Focus first error field if visible
        const firstField = errors[0].field;
        if (this._isElementVisible(firstField)) {
            firstField.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            // Delay focus to allow scroll
            setTimeout(() => {
                firstField.focus();
            }, 300);
        }
    }

    /**
     * Check if element is visible in viewport
     * @private
     */
    _isElementVisible(element) {
        const rect = element.getBoundingClientRect();
        const style = window.getComputedStyle(element);
        
        return (
            style.display !== 'none' &&
            style.visibility !== 'hidden' &&
            style.opacity !== '0' &&
            rect.width > 0 &&
            rect.height > 0
        );
    }

    /**
     * Collect form data including files
     * @private
     */
    _collectFormData(form, category) {
        const formData = new FormData();

        // CSRF token
        const csrfInput = form.querySelector('input[type="hidden"][name^="csrf_"]');
        if (csrfInput) {
            formData.append(csrfInput.name, csrfInput.value);
        }

        formData.append('category', category);

        // Form fields
        const inputs = form.querySelectorAll('input:not([type="file"]), select, textarea');
        inputs.forEach(input => {
            const name = input.name;
            
            if (!name || name === 'category') return;
            if (input.type === 'hidden' && name.startsWith('csrf_')) return;

            // Handle different input types
            if (input.type === 'checkbox') {
                if (input.checked) {
                    formData.append(name, input.value || 'on');
                }
            } else if (input.type === 'radio') {
                if (input.checked) {
                    formData.append(name, input.value);
                }
            } else {
                const trimmed = input.value.trim();
                if (trimmed) {
                    formData.append(name, trimmed);
                }
            }
        });

        // Attached files
        const files = FileManager.getFiles(category);
        files.forEach((file) => {
            formData.append('attachments[]', file, file.name);
        });

        // Log FormData for debugging
        this._logFormData(formData);

        return formData;
    }

    /**
     * Log FormData contents
     * @private
     */
    _logFormData(formData) {
        Logger.group('📦 FormData Summary');
        
        let fieldCount = 0;
        let fileCount = 0;
        
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                Logger.log(`${key}: [File] ${value.name} (${(value.size / 1024).toFixed(2)} KB)`);
                fileCount++;
            } else {
                Logger.log(`${key}:`, value);
                fieldCount++;
            }
        }
        
        Logger.log(`📊 Total: ${fieldCount} fields, ${fileCount} files`);
        Logger.groupEnd();
    }

    /**
     * Reset form to initial state
     * @private
     */
    _resetForm(form, category) {
        form.reset();
        this._clearFormErrors(form);
        
        // Clear file list
        const fileList = form.querySelector('.file-list');
        if (fileList) {
            fileList.innerHTML = '';
        }
        
        // Clear stored files
        FileManager.clearFiles(category);

        // Reset file input
        const fileInput = form.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.value = '';
        }

        // Reset custom selects if any
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            select.selectedIndex = 0;
        });
    }

    /**
     * Show field error message
     * @private
     */
    _showFieldError(field, message) {
        this._clearFieldError(field);
        addClass(field, 'error');

        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error-message';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${this._escapeHtml(message)}`;

        const formGroup = field.closest('.form-group');
        if (formGroup) {
            formGroup.appendChild(errorDiv);
        } else {
            field.parentNode.insertBefore(errorDiv, field.nextSibling);
        }

        // Add aria-invalid for accessibility
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', 'error-' + field.name);
        errorDiv.id = 'error-' + field.name;
    }

    /**
     * Clear field error
     * @private
     */
    _clearFieldError(field) {
        removeClass(field, 'error');
        field.removeAttribute('aria-invalid');
        field.removeAttribute('aria-describedby');
        
        const formGroup = field.closest('.form-group');
        const errorMsg = formGroup?.querySelector('.field-error-message');
        if (errorMsg) errorMsg.remove();
    }

    /**
     * Clear all form errors
     * @private
     */
    _clearFormErrors(form) {
        $$('.error', form).forEach(el => {
            removeClass(el, 'error');
            el.removeAttribute('aria-invalid');
            el.removeAttribute('aria-describedby');
        });
        $$('.field-error-message', form).forEach(el => el.remove());
    }

    /**
     * Handle server-side validation errors
     * @private
     */
    _handleServerErrors(form, result) {
        let errorMessage = result.message || 'An error occurred';

        if (result.errors && typeof result.errors === 'object') {
            Object.entries(result.errors).forEach(([field, msgs]) => {
                const fieldElement = form.querySelector(`[name="${field}"]`);
                const messages = Array.isArray(msgs) ? msgs : [msgs];
                
                if (fieldElement) {
                    this._showFieldError(fieldElement, messages.join(', '));
                }
            });

            // Collect all error messages
            const errorList = Object.values(result.errors).flat();
            errorMessage = errorList.join('\n');
        }

        Toast.error(errorMessage);
    }

    /**
     * Enable real-time validation on form fields
     * @private
     */
    _enableRealtimeValidation(form) {
        const fields = $$('input:not([type="hidden"]):not([type="file"]), select, textarea', form);

        fields.forEach(field => {
            // Validate on blur (only if field has value or is required)
            const blurHandler = () => {
                if (field.value.trim() || field.hasAttribute('required')) {
                    const result = ValidationService.validateField(field);
                    if (!result.valid) {
                        this._showFieldError(field, result.message);
                    } else {
                        this._clearFieldError(field);
                    }
                }
            };

            // Clear error on input
            const inputHandler = () => {
                if (field.classList.contains('error')) {
                    this._clearFieldError(field);
                }
            };

            // Validate select on change
            const changeHandler = () => {
                const result = ValidationService.validateField(field);
                if (!result.valid) {
                    this._showFieldError(field, result.message);
                } else {
                    this._clearFieldError(field);
                }
            };

            field.addEventListener('blur', blurHandler);
            field.addEventListener('input', inputHandler);
            
            if (field.tagName === 'SELECT') {
                field.addEventListener('change', changeHandler);
            }

            // Store handlers for potential cleanup
            if (!this.validationListeners.has(field)) {
                this.validationListeners.set(field, {
                    blur: blurHandler,
                    input: inputHandler,
                    change: changeHandler
                });
            }
        });
    }

    /**
     * Escape HTML to prevent XSS
     * @private
     */
    _escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Bind form submit events
     * @private
     */
    _bindEvents() {
        $$('.ticket-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submit(form);
            });

            this._enableRealtimeValidation(form);
        });
    }
}

export default new FormManager();