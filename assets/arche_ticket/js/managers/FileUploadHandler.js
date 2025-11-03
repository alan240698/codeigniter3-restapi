import FileManager from './FileManager.js';
import Toast from '../components/Toast.js';
import EventBus from '../core/EventBus.js';
import { $$, addClass, removeClass } from '../utils/dom.js';
import Logger from '../utils/logger.js';

class FileUploadHandler {
    init() {
        this._bindEvents();
    }

    _bindEvents() {
        $$('.file-upload-wrapper').forEach(wrapper => {
            const input = wrapper.querySelector('input[type="file"]');
            const form = wrapper.closest('form');
            
            if (!form || !input) {
                Logger.warn('File upload wrapper missing form or input');
                return;
            }

            const formId = form.dataset.category;
            const fileList = wrapper.nextElementSibling;

            // File input change
            input.addEventListener('change', (e) => {
                this._handleFileSelect(e.target.files, formId, fileList, wrapper);
                e.target.value = ''; // Reset input
            });

            // Drag and drop events
            this._setupDragAndDrop(wrapper, formId, fileList);

            // Click to trigger file input
            wrapper.addEventListener('click', (e) => {
                if (e.target === wrapper || e.target.closest('.file-upload-label')) {
                    input.click();
                }
            });
        });
    }

    _handleFileSelect(files, formId, fileListContainer, wrapper) {
        const filesArray = Array.from(files);
        let successCount = 0;
        let errors = [];

        filesArray.forEach(file => {
            const result = FileManager.addFile(file, formId, fileListContainer);
            
            if (result.success) {
                successCount++;
            } else {
                errors.push(result.message);
            }
        });

        // Show feedback
        if (successCount > 0) {
            Toast.success(`Đã thêm ${successCount} file`);
            removeClass(wrapper, 'error');
        }

        if (errors.length > 0) {
            addClass(wrapper, 'error');
            Toast.error(errors[0]); // Show first error
            
            setTimeout(() => {
                removeClass(wrapper, 'error');
            }, 3000);
        }
    }

    _setupDragAndDrop(wrapper, formId, fileList) {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            wrapper.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        // Highlight on drag over
        wrapper.addEventListener('dragenter', () => {
            addClass(wrapper, 'dragover');
        });

        wrapper.addEventListener('dragover', () => {
            addClass(wrapper, 'dragover');
        });

        wrapper.addEventListener('dragleave', (e) => {
            // Only remove if leaving the wrapper itself
            if (e.target === wrapper) {
                removeClass(wrapper, 'dragover');
            }
        });

        // Handle drop
        wrapper.addEventListener('drop', (e) => {
            removeClass(wrapper, 'dragover');
            
            const files = e.dataTransfer.files;
            this._handleFileSelect(files, formId, fileList, wrapper);
        });
    }
}

export default new FileUploadHandler();