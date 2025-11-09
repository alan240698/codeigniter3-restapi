import { CONFIG } from '../config/constants.js';
import StorageService from '../services/StorageService.js';
import ValidationService from '../services/ValidationService.js';
import { formatFileSize, getFileIcon } from '../utils/format.js';
import { createElement } from '../utils/dom.js';
import EventBus from '../core/EventBus.js';
import Logger from '../utils/logger.js';

class FileManager {
    constructor() {
        this.animationDelay = 10;
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

        const fileCount = StorageService.getFileCount(formId);

        // Check max files limit
        if (fileCount >= CONFIG.FILE.MAX_FILES) {
            return {
                success: false,
                message: `Maximum ${CONFIG.FILE.MAX_FILES} files allowed`
            };
        }

        // Validate file
        const validation = ValidationService.validateFile(file);
        if (!validation.valid) {
            return {
                success: false,
                message: validation.errors.join('\n')
            };
        }

        // Check duplicate by name
        if (StorageService.hasFile(formId, file.name)) {
            return {
                success: false,
                message: `File "${file.name}" already added`
            };
        }

        try {
            // Add to storage
            StorageService.addFiles(formId, [file]);

            // Render file item in UI
            this._renderFileItem(file, formId, fileListContainer);

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
        try {
            StorageService.removeFile(formId, fileName);
            
            // Fade out animation before removal
            fileItem.style.opacity = '0';
            fileItem.style.transform = 'translateX(20px)';
            
            setTimeout(() => {
                fileItem.remove();
            }, 200);

            EventBus.emit('file:removed', { formId, fileName });
            
            // Import Toast dynamically to avoid circular dependency
            import('../components/Toast.js').then(({ default: Toast }) => {
                Toast.success(`Removed "${fileName}"`);
            }).catch(err => {
                Logger.warn('Toast not available', err);
            });
        } catch (error) {
            Logger.error('Failed to remove file:', error, { formId, fileName });
            
            // Show error toast
            import('../components/Toast.js').then(({ default: Toast }) => {
                Toast.error('Failed to remove file');
            }).catch(() => {});
        }
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

    /**
     * Render file item in container with animation
     * @private
     */
    _renderFileItem(file, formId, container) {
        const fileNameEscaped = file.name.replace(/"/g, '&quot;');
        
        const html = `
            <div class="file-item" data-file-name="${fileNameEscaped}" style="opacity: 0; transform: translateY(-10px); transition: all 0.2s ease;">
                <div class="file-info">
                    <i class="fas ${getFileIcon(file.name)} file-icon"></i>
                    <div class="file-details">
                        <div class="file-name" title="${fileNameEscaped}">${fileNameEscaped}</div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="file-remove" aria-label="Remove ${fileNameEscaped}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        try {
            const fileItem = createElement(html);
            
            // Add remove handler
            const removeBtn = fileItem.querySelector('.file-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.removeFile(file.name, formId, fileItem);
                });
            }

            container.appendChild(fileItem);

            // Trigger entrance animation
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    fileItem.style.opacity = '1';
                    fileItem.style.transform = 'translateY(0)';
                });
            });
        } catch (error) {
            Logger.error('Failed to render file item:', error, { fileName: file.name });
            throw error;
        }
    }
}

export default new FileManager();