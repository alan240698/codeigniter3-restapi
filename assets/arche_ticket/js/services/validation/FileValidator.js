import { CONFIG } from '../../config/constants.js';
import { formatFileSize, getFileExtension } from '../../utils/format.js';

class FileValidator {
    validateSingle(file) {
        if (!file || !(file instanceof File)) {
            return { valid: false, errors: ['Invalid file object'] };
        }

        const errors = [];

        this._validateSize(file, errors);
        this._validateType(file, errors);
        this._validateName(file, errors);

        return {
            valid: errors.length === 0,
            errors
        };
    }

    validateMultiple(files, maxFiles = CONFIG.FILE.MAX_FILES) {
        const filesArray = Array.from(files);
        const errors = [];

        if (filesArray.length > maxFiles) {
            errors.push(`Maximum ${maxFiles} files allowed`);
            return { valid: false, errors };
        }

        filesArray.forEach(file => {
            const result = this.validateSingle(file);
            if (!result.valid) {
                errors.push(...result.errors);
            }
        });

        return {
            valid: errors.length === 0,
            errors
        };
    }

    _validateSize(file, errors) {
        if (file.size === 0) {
            errors.push(`File "${file.name}" is empty`);
        } else if (file.size > CONFIG.FILE.MAX_SIZE) {
            errors.push(
                `File "${file.name}" exceeds maximum size of ${formatFileSize(CONFIG.FILE.MAX_SIZE)}`
            );
        }
    }

    _validateType(file, errors) {
        const ext = getFileExtension(file.name).toLowerCase();
        if (!CONFIG.FILE.ALLOWED_EXTENSIONS.includes(ext)) {
            errors.push(
                `File format .${ext} is not supported. Allowed: ${CONFIG.FILE.ALLOWED_EXTENSIONS.join(', ')}`
            );
        }
    }

    _validateName(file, errors) {
        if (file.name.length > 255) {
            errors.push('File name too long (maximum 255 characters)');
        }

        if (/[<>:"|?*]/.test(file.name)) {
            errors.push('File name contains invalid characters');
        }
    }
}

export default FileValidator;