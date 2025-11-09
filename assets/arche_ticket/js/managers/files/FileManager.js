import StorageService from '../../services/storage/index.js';
import FileValidator from './FileValidator.js';
import FileRenderer from './FileRenderer.js';
import FileRemover from './FileRemover.js';
import EventBus from '../../core/EventBus.js';
import Logger from '../../utils/logger.js';

/**
 * Main file manager - coordinates file operations
 */
class FileManager {
    constructor() {
        this.validator = new FileValidator();
        this.renderer = new FileRenderer();
        this.remover = new FileRemover();
    }

    /**
     * Add file to storage and render in UI
     * @param {File} file - File object to add
     * @param {string} formId - Form identifier
     * @param {HTMLElement} fileListContainer - Container to render file item
     * @returns {Object} Result object with success status and message
     */
    addFile(file, formId, fileListContainer) {
        // Validate container exists
        if (!fileListContainer) {
            Logger.error('File list container not found', { formId });
            return {
                success: false,
                message: 'Unable to display file list'
            };
        }

        // Validate file addition
        const validation = this.validator.validateFileAddition(file, formId);
        if (!validation.valid) {
            return {
                success: false,
                message: validation.message
            };
        }

        try {
            // Add to storage
            StorageService.addFiles(formId, [file]);

            // Render file item in UI
            this.renderer.renderFileItem(
                file, 
                formId, 
                fileListContainer,
                (fileName, fileItem) => this.removeFile(fileName, formId, fileItem)
            );

            // Emit event for tracking
            EventBus.emit('file:added', { formId, file });

            return { success: true };
        } catch (error) {
            Logger.error('Failed to add file:', error, { formId, fileName: file.name });
            return {
                success: false,
                message: 'Failed to add file. Please try again.'
            };
        }
    }

    /**
     * Remove file from storage and UI
     */
    removeFile(fileName, formId, fileItem) {
        this.remover.removeFile(fileName, formId, fileItem);
    }

    /**
     * Clear all files for a form
     */
    clearFiles(formId) {
        try {
            StorageService.clearFiles(formId);
            EventBus.emit('files:cleared', { formId });
        } catch (error) {
            Logger.error('Failed to clear files:', error, { formId });
        }
    }

    /**
     * Get all files for a form
     */
    getFiles(formId) {
        return StorageService.getFiles(formId) || [];
    }
}

export default new FileManager();