import ApiService from '../services/ApiService.js';
import ValidationService from '../services/ValidationService.js';
import FileManager from './FileManager.js';
import StorageService from '../services/StorageService.js';
import Toast from '../components/Toast.js';
import EventBus from '../core/EventBus.js';
import Logger from '../utils/logger.js';
import { $$, addClass, removeClass } from '../utils/dom.js';

class FormManager {
    init() {
        this._bindEvents();
    }

    async submit(form) {
        const category = form.dataset.category;
        const btn = form.querySelector('.submit-btn');
        const btnText = btn.querySelector('span');
        const originalText = btnText.textContent;

        // Clear previous errors
        this._clearFormErrors(form);

        // Validate form
        const validation = ValidationService.validateForm(form);
        if (!validation.valid) {
            validation.errors.forEach(({ field, message }) => {
                this._showFieldError(field, message);
            });

            Toast.error(validation.errors[0].message);
            
            // Focus first error
            validation.errors[0].field.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            validation.errors[0].field.focus();
            
            return;
        }

        // Disable button
        btn.disabled = true;
        btnText.textContent = 'Sending...';
        btn.innerHTML = `<div class="spinner"></div><span>${btnText.textContent}</span>`;

        try {
            const formData = this._collectFormData(form, category);
            const result = await ApiService.createTicket(formData);

            if (result.success) {
                const ticketId = result.ticket_id || 'N/A';
                Toast.success(`✅ Ticket #${ticketId} has been created successfully!`);

                this._resetForm(form, category);
                
                EventBus.emit('ticket:created', result);
                
                // Collapse card after delay
                setTimeout(() => {
                    EventBus.emit('card:collapse');
                }, 1500);
            } else {
                this._handleServerErrors(form, result);
            }
        } catch (error) {
            Logger.error('Form submit error:', error);
            Toast.error(error.message || 'Có lỗi xảy ra. Vui lòng thử lại!');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-paper-plane"></i><span>${originalText}</span>`;
        }
    }

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
            const value = input.value;

            if (!name || name === 'category') return;
            if (input.type === 'hidden' && name.startsWith('csrf_')) return;

            if (input.type === 'checkbox') {
                if (input.checked) {
                    formData.append(name, value || 'on');
                }
            } else if (input.type === 'radio') {
                if (input.checked) {
                    formData.append(name, value);
                }
            } else {
                const trimmed = value.trim();
                if (trimmed) {
                    formData.append(name, trimmed);
                }
            }
        });

        // Files
        const files = FileManager.getFiles(category);
        files.forEach((file) => {
            formData.append('attachments[]', file, file.name);
        });

        Logger.group('📦 FormData Summary');
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                Logger.log(`${key}: [File] ${value.name}`);
            } else {
                Logger.log(`${key}:`, value);
            }
        }
        Logger.groupEnd();

        return formData;
    }

    _resetForm(form, category) {
        form.reset();
        this._clearFormErrors(form);
        
        const fileList = form.querySelector('.file-list');
        if (fileList) fileList.innerHTML = '';
        
        FileManager.clearFiles(category);
    }

    _showFieldError(field, message) {
        this._clearFieldError(field);
        addClass(field, 'error');

        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error-message';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;

        const formGroup = field.closest('.form-group');
        if (formGroup) {
            formGroup.appendChild(errorDiv);
        } else {
            field.parentNode.insertBefore(errorDiv, field.nextSibling);
        }
    }

    _clearFieldError(field) {
        removeClass(field, 'error');
        const formGroup = field.closest('.form-group');
        const errorMsg = formGroup?.querySelector('.field-error-message');
        if (errorMsg) errorMsg.remove();
    }

    _clearFormErrors(form) {
        $$('.error', form).forEach(el => removeClass(el, 'error'));
        $$('.field-error-message', form).forEach(el => el.remove());
    }

    _handleServerErrors(form, result) {
        let errorMessage = result.message || 'Có lỗi xảy ra';

        if (result.errors && typeof result.errors === 'object') {
            Object.entries(result.errors).forEach(([field, msgs]) => {
                const fieldElement = form.querySelector(`[name="${field}"]`);
                const messages = Array.isArray(msgs) ? msgs : [msgs];
                if (fieldElement) {
                    this._showFieldError(fieldElement, messages.join(', '));
                }
            });

            const errorList = Object.values(result.errors).flat();
            errorMessage = errorList.join('\n');
        }

        Toast.error(errorMessage);
    }

    _enableRealtimeValidation(form) {
        const fields = $$('input:not([type="hidden"]):not([type="file"]), select, textarea', form);

        fields.forEach(field => {
            field.addEventListener('blur', () => {
                if (field.value.trim() || field.hasAttribute('required')) {
                    const result = ValidationService.validateField(field);
                    if (!result.valid) {
                        this._showFieldError(field, result.message);
                    }
                }
            });

            field.addEventListener('input', () => {
                if (field.classList.contains('error')) {
                    this._clearFieldError(field);
                }
            });

            if (field.tagName === 'SELECT') {
                field.addEventListener('change', () => {
                    const result = ValidationService.validateField(field);
                    if (!result.valid) {
                        this._showFieldError(field, result.message);
                    }
                });
            }
        });
    }

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