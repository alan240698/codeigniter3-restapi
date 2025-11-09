import { CONFIG } from '../../config/constants.js';
import StorageService from '../../services/storage/index.js';
import ValidationService from '../../services/validation/index.js';

/**
 * Handles file validation logic
 */
class FileValidator {
    /**
     * Validate if a file can be added to a form
     * @param {File} file - File to validate
     * @param {string} formId - Form identifier
     * @returns {Object} Validation result with valid flag and message
     */
    validateFileAddition(file, formId) {
        // Check max files limit
        const fileCount = StorageService.getFileCount(formId);
        if (fileCount >= CONFIG.FILE.MAX_FILES) {
            return {
                valid: false,
                message: `Maximum ${CONFIG.FILE.MAX_FILES} files allowed`
            };
        }

        // Validate file (size, type, etc.)
        const validation = ValidationService.validateFile(file);
        if (!validation.valid) {
            return {
                valid: false,
                message: validation.errors.join('\n')
            };
        }

        // Check duplicate by name
        if (StorageService.hasFile(formId, file.name)) {
            return {
                valid: false,
                message: `File "${file.name}" already added`
            };
        }

        return { valid: true };
    }
}

export default FileValidator;