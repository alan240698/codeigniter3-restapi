import ValidationService from '../../services/validation/index.js';
import Toast from '../../components/Toast.js';
import Logger from '../../utils/logger.js';
import { $$, addClass, removeClass } from '../../utils/dom.js';

class FormValidationManager {
    constructor() {
        this.validationListeners = new WeakMap();
    }

    /**
     * Validate entire form
     */
    validateForm(form) {
        const validation = ValidationService.validateForm(form);
        
        if (!validation.valid) {
            this.showValidationErrors(form, validation.errors);
        }
        
        return validation;
    }

    /**
     * Show validation errors
     */
    showValidationErrors(form, errors) {
        errors.forEach(({ field, message }) => {
            this.showFieldError(field, message);
        });

        Toast.error(errors[0].message);
        
        // Focus first error field if visible
        const firstField = errors[0].field;
        if (this._isElementVisible(firstField)) {
            firstField.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            setTimeout(() => {
                firstField.focus();
            }, 300);
        }
    }

    /**
     * Show field error message
     */
    showFieldError(field, message) {
        this.clearFieldError(field);
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
     */
    clearFieldError(field) {
        removeClass(field, 'error');
        field.removeAttribute('aria-invalid');
        field.removeAttribute('aria-describedby');
        
        const formGroup = field.closest('.form-group');
        const errorMsg = formGroup?.querySelector('.field-error-message');
        if (errorMsg) errorMsg.remove();
    }

    /**
     * Clear all form errors
     */
    clearFormErrors(form) {
        $$('.error', form).forEach(el => {
            removeClass(el, 'error');
            el.removeAttribute('aria-invalid');
            el.removeAttribute('aria-describedby');
        });
        $$('.field-error-message', form).forEach(el => el.remove());
    }

    /**
     * Handle server-side validation errors
     */
    handleServerErrors(form, result) {
        let errorMessage = result.message || 'An error occurred';

        if (result.errors && typeof result.errors === 'object') {
            Object.entries(result.errors).forEach(([field, msgs]) => {
                const fieldElement = form.querySelector(`[name="${field}"]`);
                const messages = Array.isArray(msgs) ? msgs : [msgs];
                
                if (fieldElement) {
                    this.showFieldError(fieldElement, messages.join(', '));
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
     */
    enableRealtimeValidation(form) {
        const fields = $$('input:not([type="hidden"]):not([type="file"]), select, textarea', form);

        fields.forEach(field => {
            const blurHandler = () => {
                if (field.value.trim() || field.hasAttribute('required')) {
                    const result = ValidationService.validateField(field);
                    if (!result.valid) {
                        this.showFieldError(field, result.message);
                    } else {
                        this.clearFieldError(field);
                    }
                }
            };

            const inputHandler = () => {
                if (field.classList.contains('error')) {
                    this.clearFieldError(field);
                }
            };

            const changeHandler = () => {
                const result = ValidationService.validateField(field);
                if (!result.valid) {
                    this.showFieldError(field, result.message);
                } else {
                    this.clearFieldError(field);
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
     * Check if element is visible in viewport
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
     * Escape HTML to prevent XSS
     */
    _escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

export default FormValidationManager;