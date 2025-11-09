import StorageService from '../../services/storage/index.js';
import EventBus from '../../core/EventBus.js';
import Logger from '../../utils/logger.js';

/**
 * Handles file removal operations
 */
class FileRemover {
    constructor() {
        this.animationDuration = 200;
    }

    /**
     * Remove file from storage and UI
     * @param {string} fileName - Name of file to remove
     * @param {string} formId - Form identifier
     * @param {HTMLElement} fileItem - DOM element of file item
     */
    removeFile(fileName, formId, fileItem) {
        try {
            // Remove from storage
            StorageService.removeFile(formId, fileName);
            
            // Animate removal
            this._animateRemoval(fileItem);

            // Emit event
            EventBus.emit('file:removed', { formId, fileName });
            
            // Show success toast
            this._showSuccessToast(fileName);
        } catch (error) {
            Logger.error('Failed to remove file:', error, { formId, fileName });
            this._showErrorToast();
        }
    }

    /**
     * Animate file removal
     * @private
     */
    _animateRemoval(fileItem) {
        fileItem.style.opacity = '0';
        fileItem.style.transform = 'translateX(20px)';
        
        setTimeout(() => {
            fileItem.remove();
        }, this.animationDuration);
    }

    /**
     * Show success toast notification
     * @private
     */
    _showSuccessToast(fileName) {
        import('../../components/Toast.js')
            .then(({ default: Toast }) => {
                Toast.success(`Removed "${fileName}"`);
            })
            .catch(err => {
                Logger.warn('Toast not available', err);
            });
    }

    /**
     * Show error toast notification
     * @private
     */
    _showErrorToast() {
        import('../../components/Toast.js')
            .then(({ default: Toast }) => {
                Toast.error('Failed to remove file');
            })
            .catch(() => {});
    }
}

export default FileRemover;