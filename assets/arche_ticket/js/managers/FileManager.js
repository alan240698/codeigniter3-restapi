import { CONFIG } from '../config/constants.js';
import StorageService from '../services/StorageService.js';
import ValidationService from '../services/ValidationService.js';
import { formatFileSize, getFileIcon } from '../utils/format.js';
import { createElement } from '../utils/dom.js';
import EventBus from '../core/EventBus.js';
import Logger from '../utils/logger.js';

class FileManager {
    addFile(file, formId, fileListContainer) {
        const fileCount = StorageService.getFileCount(formId);

        // Check max files
        if (fileCount >= CONFIG.FILE.MAX_FILES) {
            return {
                success: false,
                message: `Only maximum uploads are allowed ${CONFIG.FILE.MAX_FILES} files`
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

        // Check duplicate
        if (StorageService.hasFile(formId, file.name)) {
            return {
                success: false,
                message: `File "${file.name}" has been added`
            };
        }

        // Add to storage
        StorageService.addFiles(formId, [file]);

        // Render file item
        this._renderFileItem(file, formId, fileListContainer);

        EventBus.emit('file:added', { formId, file });

        return { success: true };
    }

    removeFile(fileName, formId, fileItem) {
        StorageService.removeFile(formId, fileName);
        fileItem.remove();

        EventBus.emit('file:removed', { formId, fileName });
    }

    clearFiles(formId) {
        StorageService.clearFiles(formId);
        EventBus.emit('files:cleared', { formId });
    }

    getFiles(formId) {
        return StorageService.getFiles(formId);
    }

    _renderFileItem(file, formId, container) {
        const html = `
            <div class="file-item" data-file-name="${file.name}">
                <div class="file-info">
                    <i class="fas ${getFileIcon(file.name)} file-icon"></i>
                    <div class="file-details">
                        <div class="file-name" title="${file.name}">${file.name}</div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="file-remove" aria-label="Remove file">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        const fileItem = createElement(html);
        
        // Add remove handler
        const removeBtn = fileItem.querySelector('.file-remove');
        removeBtn.addEventListener('click', () => {
            this.removeFile(file.name, formId, fileItem);
        });

        container.appendChild(fileItem);

        // Animation
        setTimeout(() => {
            fileItem.style.opacity = '1';
            fileItem.style.transform = 'translateY(0)';
        }, 10);
    }
}

export default new FileManager();