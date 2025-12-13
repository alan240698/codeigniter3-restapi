import { addClass, removeClass } from '../../utils/dom.js';

class DragDropManager {
    constructor(eventManager) {
        this.eventManager = eventManager;
        this.dragCounter = new Map();
    }

    setup(wrapper, formId, fileList, onFilesDropped) {
        const wrapperId = this._getWrapperId(wrapper);
        this.dragCounter.set(wrapperId, 0);

        this._preventDefaults(wrapper);
        this._setupDragEnter(wrapper, wrapperId);
        this._setupDragOver(wrapper);
        this._setupDragLeave(wrapper, wrapperId);
        this._setupDrop(wrapper, wrapperId, onFilesDropped);
    }

    cleanup() {
        this.dragCounter.clear();
    }

    _getWrapperId(wrapper) {
        if (!wrapper.dataset.wrapperId) {
            wrapper.dataset.wrapperId = `upload-${Math.random().toString(36).substr(2, 9)}`;
        }
        return wrapper.dataset.wrapperId;
    }

    _preventDefaults(wrapper) {
        const preventDefaults = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.eventManager.add(wrapper, eventName, preventDefaults);
        });
    }

    _setupDragEnter(wrapper, wrapperId) {
        const handler = () => {
            const count = this.dragCounter.get(wrapperId) || 0;
            this.dragCounter.set(wrapperId, count + 1);

            if (count === 0) {
                addClass(wrapper, 'dragover');
            }
        };
        
        this.eventManager.add(wrapper, 'dragenter', handler);
    }

    _setupDragOver(wrapper) {
        const handler = () => {
            addClass(wrapper, 'dragover');
        };

        this.eventManager.add(wrapper, 'dragover', handler);
    }

    _setupDragLeave(wrapper, wrapperId) {
        const handler = () => {
            const count = this.dragCounter.get(wrapperId) || 0;
            this.dragCounter.set(wrapperId, Math.max(0, count - 1));

            if (count - 1 <= 0) {
                removeClass(wrapper, 'dragover');
            }
        };

        this.eventManager.add(wrapper, 'dragleave', handler);
    }

    _setupDrop(wrapper, wrapperId, onFilesDropped) {
        const handler = (e) => {
            this.dragCounter.set(wrapperId, 0);
            removeClass(wrapper, 'dragover');

            const files = e.dataTransfer?.files;
            if (files && files.length > 0) {
                onFilesDropped(files);
            }
        };

        this.eventManager.add(wrapper, 'drop', handler);
    }
}

export default DragDropManager;
