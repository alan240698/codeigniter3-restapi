import FileManager from '../files/index.js';
import Logger from '../../utils/logger.js';

class FormResetManager {
    constructor(validationManager) {
        this.validationManager = validationManager;
    }

    /**
     * Reset form to initial state
     */
    resetForm(form, category) {
        form.reset();
        this.validationManager.clearFormErrors(form);
        
        this._clearFileList(form);
        this._clearStoredFiles(category);
        this._resetFileInput(form);
        this._resetCustomSelects(form);
        
        Logger.info('Form reset', { category });
    }

    /**
     * Reset form when switching to another form
     */
    resetFormOnSwitch(form, category) {
        form.reset();
        
        this.validationManager.clearFormErrors(form);
        this._clearFileList(form);
        this._clearStoredFiles(category);
        this._resetFileInput(form);
        
        Logger.info('Form reset on switch', { category });
    }

    /**
     * Clear file list UI
     */
    _clearFileList(form) {
        const fileList = form.querySelector('.file-list');
        if (fileList) {
            fileList.innerHTML = '';
        }
    }

    /**
     * Clear stored files
     */
    _clearStoredFiles(category) {
        FileManager.clearFiles(category);
    }

    /**
     * Reset file input
     */
    _resetFileInput(form) {
        const fileInput = form.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.value = '';
        }
    }

    /**
     * Reset custom selects
     */
    _resetCustomSelects(form) {
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            select.selectedIndex = 0;
        });
    }
}

export default FormResetManager;