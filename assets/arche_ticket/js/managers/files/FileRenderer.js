import { formatFileSize, getFileIcon } from '../../utils/format.js';
import { createElement } from '../../utils/dom.js';
import Logger from '../../utils/logger.js';

/**
 * Handles file UI rendering
 */
class FileRenderer {
    /**
     * Render file item in container with animation
     * @param {File} file - File object
     * @param {string} formId - Form identifier
     * @param {HTMLElement} container - Container element
     * @param {Function} onRemove - Callback when remove button is clicked
     */
    renderFileItem(file, formId, container, onRemove) {
        const fileNameEscaped = this._escapeFileName(file.name);
        const html = this._createFileItemHTML(file, fileNameEscaped);

        try {
            const fileItem = createElement(html);
            
            // Add remove handler
            this._attachRemoveHandler(fileItem, file.name, onRemove);

            container.appendChild(fileItem);

            // Trigger entrance animation
            this._animateEntrance(fileItem);
        } catch (error) {
            Logger.error('Failed to render file item:', error, { fileName: file.name });
            throw error;
        }
    }

    /**
     * Escape file name for safe HTML rendering
     * @private
     */
    _escapeFileName(fileName) {
        return fileName.replace(/"/g, '&quot;');
    }

    /**
     * Create HTML for file item
     * @private
     */
    _createFileItemHTML(file, fileNameEscaped) {
        return `
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
    }

    /**
     * Attach remove button handler
     * @private
     */
    _attachRemoveHandler(fileItem, fileName, onRemove) {
        const removeBtn = fileItem.querySelector('.file-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                onRemove(fileName, fileItem);
            });
        }
    }

    /**
     * Animate file item entrance
     * @private
     */
    _animateEntrance(fileItem) {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                fileItem.style.opacity = '1';
                fileItem.style.transform = 'translateY(0)';
            });
        });
    }
}

export default FileRenderer;