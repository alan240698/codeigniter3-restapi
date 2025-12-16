class FormValidationManager {
    constructor(rules) {
        this.rules = rules;
        this.errors = {};
        this.counters = new Map();
        this.typingTimers = new Map();
        this.validationStates = new Map();
    }

    // ==================== VALIDATION METHODS ====================

    validate(fieldName, value, allRules = this.rules) {
        const rules = allRules[fieldName];
        if (!rules) return null;

        // Required validation
        if (rules.required && (!value || value.toString().trim() === '')) {
            return rules.message.required;
        }

        // Skip other validations if field is empty and not required
        if (!rules.required && (!value || value.toString().trim() === '')) {
            return null;
        }

        const stringValue = value.toString().trim();

        // Min length validation
        if (rules.minLength && stringValue.length < rules.minLength) {
            return rules.message.minLength;
        }

        // Max length validation
        if (rules.maxLength && stringValue.length > rules.maxLength) {
            return rules.message.maxLength;
        }

        // Pattern validation
        if (rules.pattern && !rules.pattern.test(stringValue)) {
            return rules.message.pattern;
        }

        return null;
    }

    validateAll(formData, allRules = this.rules) {
        this.errors = {};
        let isValid = true;

        for (const fieldName in allRules) {
            const error = this.validate(fieldName, formData[fieldName], allRules);
            if (error) {
                this.errors[fieldName] = error;
                isValid = false;
            }
        }

        return { isValid, errors: this.errors };
    }

    showError(fieldId, message, animate = true) {
        const input = document.getElementById(fieldId);
        if (!input) return;

        // Check if the error message is different
        const existingError = document.getElementById(`${fieldId}-error`);
        if (existingError && existingError.textContent === message) {
            return;
        }

        // Remove existing error
        this.clearError(fieldId, false);

        // Add error class to input
        input.classList.add('is-invalid');
        input.style.borderColor = '#dc3545';
        input.style.transition = 'border-color 0.2s ease';

        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.style.cssText = `
            display: block;
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
            transition: opacity 0.2s ease, transform 0.2s ease;
            ${animate ? 'animation: slideDown 0.2s ease;' : ''}
        `;
        errorDiv.textContent = message;
        errorDiv.id = `${fieldId}-error`;

        // Insert error message after input (after counter if exists)
        const counter = document.getElementById(`${fieldId}-counter`);
        if (counter) {
            counter.parentNode.insertBefore(errorDiv, counter.nextSibling);
        } else {
            input.parentNode.insertBefore(errorDiv, input.nextSibling);
        }

        // Add shake animation only on blur or submit, not during typing
        if (animate) {
            input.style.animation = 'shake 0.3s ease';
            setTimeout(() => {
                input.style.animation = '';
            }, 300);
        }
    }

    clearError(fieldId, animate = true) {
        const input = document.getElementById(fieldId);
        if (!input) return;

        // Remove error class with transition
        input.classList.remove('is-invalid');
        input.style.borderColor = '';

        // Remove error message with fade out
        const errorDiv = document.getElementById(`${fieldId}-error`);
        if (errorDiv) {
            if (animate) {
                errorDiv.style.opacity = '0';
                errorDiv.style.transform = 'translateY(-5px)';
                setTimeout(() => errorDiv.remove(), 200);
            } else {
                errorDiv.remove();
            }
        }
    }

    showSuccess(fieldId, temporary = true) {
        const input = document.getElementById(fieldId);
        if (!input) return;

        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        input.style.borderColor = '#28a745';
        input.style.transition = 'border-color 0.2s ease';

        // Clear success style after delay
        if (temporary) {
            setTimeout(() => {
                input.classList.remove('is-valid');
                input.style.borderColor = '';
            }, 1500);
        }
    }

    clearAllErrors(fieldIds) {
        fieldIds.forEach(fieldId => {
            this.clearError(fieldId, false);
        });
    }

    // ==================== CHARACTER COUNTER METHODS ====================

    addCounter(inputId, maxLength) {
        const input = document.getElementById(inputId);
        if (!input) return;

        // Remove existing counter if any
        this.removeCounter(inputId);

        const counter = document.createElement('div');
        counter.className = 'character-counter';
        counter.style.cssText = `
            font-size: 12px;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
            transition: all 0.3s ease;
        `;
        counter.id = `${inputId}-counter`;

        // Store counter reference
        this.counters.set(inputId, { element: counter, maxLength });

        this.updateCounter(inputId);

        input.parentNode.insertBefore(counter, input.nextSibling);

        // Add event listener
        const updateHandler = () => this.updateCounter(inputId);
        input.addEventListener('input', updateHandler);

        // Store handler for cleanup
        counter.dataset.handler = 'attached';
    }

    updateCounter(inputId) {
        const input = document.getElementById(inputId);
        const counterData = this.counters.get(inputId);

        if (!input || !counterData) return;

        const { element: counter, maxLength } = counterData;
        const currentLength = input.value.length;
        const percentage = (currentLength / maxLength) * 100;

        counter.textContent = `${currentLength} / ${maxLength} characters`;

        if (percentage >= 90) {
            counter.style.color = '#dc3545';
            counter.style.fontWeight = 'bold';
        } else if (percentage >= 70) {
            counter.style.color = '#ffc107';
            counter.style.fontWeight = 'normal';
        } else {
            counter.style.color = '#6c757d';
            counter.style.fontWeight = 'normal';
        }
    }

    removeCounter(inputId) {
        const existingCounter = document.getElementById(`${inputId}-counter`);
        if (existingCounter) {
            existingCounter.remove();
        }
        this.counters.delete(inputId);
    }

    removeAllCounters() {
        this.counters.forEach((_, inputId) => {
            this.removeCounter(inputId);
        });
    }

    // ==================== SETUP METHODS ====================

    setupFieldValidation(fieldId, fieldName, options = {}) {
        const input = document.getElementById(fieldId);
        if (!input) return;

        const typingDelay = options.typingDelay || 400;
        let isFirstInput = true;

        // Real-time validation
        input.addEventListener('input', () => {
            const existingError = document.getElementById(`${fieldId}-error`);
            if (existingError && isFirstInput) {
                this.clearError(fieldId, false);
                isFirstInput = false;
            }

            // Clear timer
            const oldTimer = this.typingTimers.get(fieldId);
            if (oldTimer) {
                clearTimeout(oldTimer);
            }

            const currentValue = input.value;
            const rules = this.rules[fieldName];

            if (rules && rules.maxLength && currentValue.length > rules.maxLength) {
                this.showError(fieldId, rules.message.maxLength, false);
                return;
            }

            const newTimer = setTimeout(() => {
                const error = this.validate(fieldName, currentValue);
                if (error) {
                    this.showError(fieldId, error, false);
                } else {
                    this.clearError(fieldId, true);
                    if (currentValue && !options.skipSuccessIndicator) {
                        this.showSuccess(fieldId, true);
                    }
                }
            }, typingDelay);

            this.typingTimers.set(fieldId, newTimer);
        });

        input.addEventListener('blur', () => {
            isFirstInput = true;

            const timer = this.typingTimers.get(fieldId);
            if (timer) {
                clearTimeout(timer);
                this.typingTimers.delete(fieldId);
            }

            const error = this.validate(fieldName, input.value);
            if (error) {
                this.showError(fieldId, error, true);
            } else {
                this.clearError(fieldId, true);
                if (input.value && !options.skipSuccessIndicator) {
                    this.showSuccess(fieldId, true);
                }
            }
        });

        // Validate khi change
        input.addEventListener('change', () => {
            const timer = this.typingTimers.get(fieldId);
            if (timer) {
                clearTimeout(timer);
                this.typingTimers.delete(fieldId);
            }

            const error = this.validate(fieldName, input.value);
            if (error) {
                this.showError(fieldId, error, true);
            } else {
                this.clearError(fieldId, true);
                if (input.value && !options.skipSuccessIndicator) {
                    this.showSuccess(fieldId, true);
                }
            }
        });

        // Auto-uppercase for code fields
        if (options.autoUppercase) {
            input.addEventListener('input', (e) => {
                const start = e.target.selectionStart;
                const end = e.target.selectionEnd;
                const value = e.target.value;
                const upperValue = value.toUpperCase();

                if (value !== upperValue) {
                    e.target.value = upperValue;
                    e.target.setSelectionRange(start, end);
                }
            });
        }

        // Add character counter if maxLength specified
        const rules = this.rules[fieldName];
        if (rules && rules.maxLength) {
            this.addCounter(fieldId, rules.maxLength);
        }
    }

    setupFormValidation(fieldMappings, options = {}) {
        fieldMappings.forEach(({ fieldId, fieldName, ...fieldOptions }) => {
            this.setupFieldValidation(fieldId, fieldName, {
                ...options,
                ...fieldOptions
            });
        });
    }

    // ==================== HELPER METHODS ====================

    validateAndShowErrors(formData, fieldMap) {
        const validation = this.validateAll(formData);

        if (!validation.isValid) {
            // Show all errors with animation
            for (const [fieldName, errorMessage] of Object.entries(validation.errors)) {
                const fieldId = fieldMap[fieldName];
                if (fieldId) {
                    this.showError(fieldId, errorMessage, true);
                }
            }

            // Scroll to first error
            const firstErrorField = Object.keys(validation.errors)[0];
            const firstErrorId = fieldMap[firstErrorField];
            if (firstErrorId) {
                document.getElementById(firstErrorId)?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            return false;
        }

        return true;
    }

    reset(fieldIds) {
        // Clear all timers
        fieldIds.forEach(fieldId => {
            const timer = this.typingTimers.get(fieldId);
            if (timer) {
                clearTimeout(timer);
                this.typingTimers.delete(fieldId);
            }

            // Reset counter
            if (this.counters.has(fieldId)) {
                this.updateCounter(fieldId);
            }

            // Clear validation states
            const input = document.getElementById(fieldId);
            if (input) {
                input.classList.remove('is-valid', 'is-invalid');
                input.style.borderColor = '';
            }
        });

        this.clearAllErrors(fieldIds);
    }
}