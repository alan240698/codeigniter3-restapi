import { CONFIG } from '../config/constants.js';
import { formatFileSize, getFileExtension } from '../utils/format.js';
import Logger from '../utils/logger.js';

class ValidationService {
    validateField(field) {
        const name = field.name;
        const value = field.value?.trim() || '';
        const label = this._getFieldLabel(field);

        // Required validation
        if (field.hasAttribute('required')) {
            if (field.type === 'checkbox' || field.type === 'radio') {
                const form = field.closest('form');
                const isChecked = form.querySelector(`[name="${name}"]:checked`);
                if (!isChecked) {
                    return { valid: false, message: `${label} là bắt buộc` };
                }
            } else if (!value) {
                return { valid: false, message: `${label} là bắt buộc` };
            }
        }

        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                return { valid: false, message: 'Email không hợp lệ' };
            }
        }

        // Number validation
        if (field.type === 'number' && value) {
            const min = field.getAttribute('min');
            const max = field.getAttribute('max');
            const numValue = parseFloat(value);

            if (min !== null && numValue < parseFloat(min)) {
                return { valid: false, message: `Giá trị tối thiểu là ${min}` };
            }
            if (max !== null && numValue > parseFloat(max)) {
                return { valid: false, message: `Giá trị tối đa là ${max}` };
            }
        }

        // Min length validation
        const minLength = field.getAttribute('minlength');
        if (minLength && value.length < parseInt(minLength)) {
            return { valid: false, message: `Độ dài tối thiểu là ${minLength} ký tự` };
        }

        return { valid: true };
    }

    validateForm(form) {
        const errors = [];
        const fields = form.querySelectorAll('input:not([type="hidden"]):not([type="file"]), select, textarea');

        fields.forEach(field => {
            const result = this.validateField(field);
            if (!result.valid) {
                errors.push({ field, message: result.message });
            }
        });

        return {
            valid: errors.length === 0,
            errors
        };
    }

    validateFile(file) {
        const errors = [];

        // Size validation
        if (file.size > CONFIG.FILE.MAX_SIZE) {
            errors.push(`File "${file.name}" vượt quá ${formatFileSize(CONFIG.FILE.MAX_SIZE)}`);
        }

        // Type validation
        const ext = getFileExtension(file.name);
        if (!CONFIG.FILE.ALLOWED_EXTENSIONS.includes(ext)) {
            errors.push(`File format .${ext} not supported`);
        }

        // Name length validation
        if (file.name.length > 255) {
            errors.push(`File name too long (maximum 255 characters)`);
        }

        return {
            valid: errors.length === 0,
            errors
        };
    }

    _getFieldLabel(field) {
        const label = field.closest('.form-group')?.querySelector('label')?.textContent;
        return label?.replace(/[*:]/g, '').trim() || field.name;
    }
}

export default new ValidationService();
