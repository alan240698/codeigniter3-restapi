import Logger from '../../utils/logger.js';

class FieldValidator {
    constructor(ruleManager, patternManager, messageManager) {
        this.ruleManager = ruleManager;
        this.patternManager = patternManager;
        this.messageManager = messageManager;
    }

    validate(field) {
        if (!field) {
            return { valid: false, message: 'Field is null' };
        }

        const name = field.name;
        const value = field.value?.trim() || '';
        const label = this._getFieldLabel(field);
        const type = field.type;

        // Custom rules first
        if (this.ruleManager.has(name)) {
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

        // Skip if empty and not required
        if (!value && !field.hasAttribute('required')) {
            return { valid: true };
        }

        // Type-specific validation
        const typeResult = this._validateByType(field, value, type);
        if (!typeResult.valid) {
            return typeResult;
        }

        // Pattern validation
        if (field.hasAttribute('pattern')) {
            const patternResult = this._validatePattern(field, value, label);
            if (!patternResult.valid) {
                return patternResult;
            }
        }

        // Length validation
        return this._validateLength(field, value, label);
    }

    _validateByType(field, value, type) {
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
                return { valid: true };
        }
    }

    _validateRequired(field, value, label) {
        const type = field.type;

        if (type === 'checkbox' || type === 'radio') {
            const form = field.closest('form');
            const name = field.name;
            const isChecked = form?.querySelector(`[name="${name}"]:checked`);

            if (!isChecked) {
                return { 
                    valid: false, 
                    message: this.messageManager.get('required', { label }) 
                };
            }
        } else if (field.tagName === 'SELECT') {
            if (!value || value === '' || value === '0') {
                return { 
                    valid: false, 
                    message: this.messageManager.get('required', { label }) 
                };
            }
        } else if (!value) {
            return { 
                valid: false, 
                message: this.messageManager.get('required', { label }) 
            };
        }

        return { valid: true };
    }

    _validateEmail(value) {
        if (!value) return { valid: true };

        const pattern = this.patternManager.get('email');
        if (!pattern.test(value)) {
            return { 
                valid: false, 
                message: this.messageManager.get('email') 
            };
        }

        return { valid: true };
    }

    _validatePhone(value) {
        if (!value) return { valid: true };

        const cleaned = value.replace(/[\s\-\(\)]/g, '');

        if (cleaned.length < 10 || cleaned.length > 15) {
            return { 
                valid: false, 
                message: this.messageManager.get('phone') 
            };
        }

        const pattern = this.patternManager.get('phone');
        if (!pattern.test(value)) {
            return { 
                valid: false, 
                message: this.messageManager.get('phone') 
            };
        }

        return { valid: true };
    }

    _validateUrl(value) {
        if (!value) return { valid: true };

        const pattern = this.patternManager.get('url');
        if (!pattern.test(value)) {
            return { 
                valid: false, 
                message: this.messageManager.get('url') 
            };
        }

        return { valid: true };
    }

    _validateNumber(field, value) {
        if (!value) return { valid: true };

        const numValue = parseFloat(value);

        if (isNaN(numValue)) {
            return { 
                valid: false, 
                message: 'Value must be a number' 
            };
        }

        const min = field.getAttribute('min');
        const max = field.getAttribute('max');

        if (min !== null && numValue < parseFloat(min)) {
            return { 
                valid: false, 
                message: this.messageManager.get('min', { min }) 
            };
        }

        if (max !== null && numValue > parseFloat(max)) {
            return { 
                valid: false, 
                message: this.messageManager.get('max', { max }) 
            };
        }

        return { valid: true };
    }

    _validatePattern(field, value, label) {
        if (!value) return { valid: true };

        const pattern = field.getAttribute('pattern');
        const regex = new RegExp(pattern);

        if (!regex.test(value)) {
            return { 
                valid: false, 
                message: this.messageManager.get('pattern', { label }) 
            };
        }

        return { valid: true };
    }

    _validateLength(field, value, label) {
        if (!value) return { valid: true };

        const minLength = field.getAttribute('minlength');
        const maxLength = field.getAttribute('maxlength');

        if (minLength && value.length < parseInt(minLength)) {
            return { 
                valid: false, 
                message: this.messageManager.get('minLength', { label, min: minLength }) 
            };
        }

        if (maxLength && value.length > parseInt(maxLength)) {
            return { 
                valid: false, 
                message: this.messageManager.get('maxLength', { label, max: maxLength }) 
            };
        }

        return { valid: true };
    }

    _validateCustomRule(field, value, label) {
        const rule = this.ruleManager.get(field.name);

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

    _getFieldLabel(field) {
        const id = field.id;
        if (id) {
            const label = document.querySelector(`label[for="${id}"]`);
            if (label) {
                return label.textContent.replace(/[*:]/g, '').trim();
            }
        }

        const formGroup = field.closest('.form-group, .field-group');
        const groupLabel = formGroup?.querySelector('label');
        if (groupLabel) {
            return groupLabel.textContent.replace(/[*:]/g, '').trim();
        }

        return field.placeholder || field.name || 'This field';
    }
}

export default FieldValidator;
