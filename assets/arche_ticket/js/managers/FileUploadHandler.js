import FileManager from './FileManager.js';
import Toast from '../components/Toast.js';
import EventBus from '../core/EventBus.js';
import { $$, addClass, removeClass } from '../utils/dom.js';
import Logger from '../utils/logger.js';

class FileUploadHandler {
    constructor() {
        this.eventListeners = new Map(); // Track listeners for cleanup
        this.dragCounter = new Map(); // Track drag enter/leave properly
    }

    init() {
        this._bindEvents();
    }

    destroy() {
        // Cleanup all event listeners
        this.eventListeners.forEach((listeners, element) => {
            listeners.forEach(({ event, handler }) => {
                element.removeEventListener(event, handler);
            });
        });
        this.eventListeners.clear();
        this.dragCounter.clear();
    }

    _bindEvents() {
        $$('.file-upload-wrapper').forEach(wrapper => {
            const input = wrapper.querySelector('input[type="file"]');
            const form = wrapper.closest('form');
            
            if (!form || !input) {
                Logger.warn('File upload wrapper missing form or input', { wrapper });
                return;
            }

            const formId = form.dataset.category;
            const fileList = wrapper.nextElementSibling;

            if (!fileList) {
                Logger.warn('File list container not found', { formId });
                return;
            }

            // File input change handler
            const changeHandler = (e) => {
                this._handleFileSelect(e.target.files, formId, fileList, wrapper);
                // Reset input to allow same file selection
                setTimeout(() => {
                    e.target.value = '';
                }, 100);
            };
            this._addEventListener(input, 'change', changeHandler);

            // Click handler to trigger file input
            const clickHandler = (e) => {
                // Only trigger if clicking wrapper or label, not buttons/other elements
                if (e.target === wrapper || 
                    e.target.closest('.file-upload-label') ||
                    e.target.classList.contains('file-upload-label')) {
                    input.click();
                }
            };
            this._addEventListener(wrapper, 'click', clickHandler);

            // Setup drag and drop
            this._setupDragAndDrop(wrapper, formId, fileList);
        });
    }

    _handleFileSelect(files, formId, fileListContainer, wrapper) {
        if (!files || files.length === 0) return;

        const filesArray = Array.from(files);
        let successCount = 0;
        const errors = [];

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
            Toast.success(`Added ${successCount} file${successCount > 1 ? 's' : ''}`);
            removeClass(wrapper, 'error');
        }

        if (errors.length > 0) {
            addClass(wrapper, 'error');
            
            // Show unique errors only
            const uniqueErrors = [...new Set(errors)];
            Toast.error(uniqueErrors[0]);
            
            setTimeout(() => {
                removeClass(wrapper, 'error');
            }, 3000);
        }
    }

    _setupDragAndDrop(wrapper, formId, fileList) {
        const wrapperId = wrapper.dataset.wrapperId || Math.random().toString(36);
        wrapper.dataset.wrapperId = wrapperId;
        this.dragCounter.set(wrapperId, 0);

        // Prevent default behaviors
        const preventDefaults = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this._addEventListener(wrapper, eventName, preventDefaults);
        });

        // Drag enter - increment counter
        const dragEnterHandler = (e) => {
            const count = this.dragCounter.get(wrapperId) || 0;
            this.dragCounter.set(wrapperId, count + 1);
            
            if (count === 0) {
                addClass(wrapper, 'dragover');
            }
        };
        this._addEventListener(wrapper, 'dragenter', dragEnterHandler);

        // Drag over - maintain highlight
        const dragOverHandler = () => {
            addClass(wrapper, 'dragover');
        };
        this._addEventListener(wrapper, 'dragover', dragOverHandler);

        // Drag leave - decrement counter
        const dragLeaveHandler = () => {
            const count = this.dragCounter.get(wrapperId) || 0;
            this.dragCounter.set(wrapperId, Math.max(0, count - 1));
            
            if (count - 1 <= 0) {
                removeClass(wrapper, 'dragover');
            }
        };
        this._addEventListener(wrapper, 'dragleave', dragLeaveHandler);

        // Drop handler
        const dropHandler = (e) => {
            this.dragCounter.set(wrapperId, 0);
            removeClass(wrapper, 'dragover');
            
            const files = e.dataTransfer?.files;
            if (files && files.length > 0) {
                this._handleFileSelect(files, formId, fileList, wrapper);
            }
        };
        this._addEventListener(wrapper, 'drop', dropHandler);
    }

    /**
     * Track event listeners for cleanup
     * @private
     */
    _addEventListener(element, event, handler) {
        if (!this.eventListeners.has(element)) {
            this.eventListeners.set(element, []);
        }
        
        this.eventListeners.get(element).push({ event, handler });
        element.addEventListener(event, handler);
    }
}

export default new FileUploadHandler();