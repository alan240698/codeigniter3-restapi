import { $$, removeClass } from '../../utils/dom.js';
import Logger from '../../utils/logger.js';
import FileManager from '../files/index.js';
import UploadEventManager from './UploadEventManager.js';
import DragDropManager from './DragDropManager.js';
import UploadFeedbackManager from './UploadFeedbackManager.js';
import UploadInteractionHandler from './UploadInteractionHandler.js';

class FileUploadManager {
    constructor() {
        this.eventManager = new UploadEventManager();
        this.dragDropManager = new DragDropManager(this.eventManager);
        this.feedbackManager = new UploadFeedbackManager();
        this.interactionHandler = new UploadInteractionHandler(this.eventManager);
    }

    init() {
        this._initializeWrappers();
    }

    destroy() {
        this.eventManager.cleanup();
        this.dragDropManager.cleanup();
    }

    _initializeWrappers() {
        $$('.file-upload-wrapper').forEach(wrapper => {
            this._setupWrapper(wrapper);
        });
    }

    _setupWrapper(wrapper) {
        const config = this._validateWrapper(wrapper);
        if (!config) return;

        this._setupFileInput(config);
        this.interactionHandler.setupClickTrigger(config.wrapper, config.input);
        this.dragDropManager.setup(
            config.wrapper, 
            config.formId, 
            config.fileList,
            (files) => this.handleFiles(files, config)
        );
    }

    _validateWrapper(wrapper) {
        const input = wrapper.querySelector('input[type="file"]');
        const form = wrapper.closest('form');
        
        if (!form || !input) {
            Logger.warn('File upload wrapper missing form or input', { wrapper });
            return null;
        }

        const formId = form.dataset.category;
        const fileList = wrapper.nextElementSibling;

        if (!fileList) {
            Logger.warn('File list container not found', { formId });
            return null;
        }

        return { wrapper, input, form, formId, fileList };
    }

    _setupFileInput(config) {
        const changeHandler = (e) => {
            this.handleFiles(e.target.files, config);
            
            // Reset input to allow same file selection
            setTimeout(() => {
                e.target.value = '';
            }, 100);
        };
        
        this.eventManager.add(config.input, 'change', changeHandler);
    }

    handleFiles(files, config) {
        if (!files || files.length === 0) return;

        const filesArray = Array.from(files);
        const results = this._processFiles(filesArray, config.formId, config.fileList);
        
        this.feedbackManager.showFeedback(results, config.wrapper);
    }

    _processFiles(filesArray, formId, fileListContainer) {
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

        return { successCount, errors };
    }
}

export default new FileUploadManager();
