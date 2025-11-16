import FileManager  from '../files/index.js';
import Logger       from '../../utils/logger.js';

class FormDataCollector {
    /**
     * Collect form data including files
     */
    collectFormData(form, category) {
        const formData = new FormData();

        // CSRF token
        this._appendCSRFToken(formData, form);

        // Category
        formData.append('category', category);

        // Form fields
        this._appendFormFields(formData, form);

        // Attached files
        this._appendFiles(formData, category);

        // Log FormData for debugging
        this._logFormData(formData);

        return formData;
    }

    /**
     * Append CSRF token
     */
    _appendCSRFToken(formData, form) {
        const csrfInput = form.querySelector('input[type="hidden"][name^="csrf_"]');
        if (csrfInput) {
            formData.append(csrfInput.name, csrfInput.value);
        }
    }

    /**
     * Append form fields
     */
    _appendFormFields(formData, form) {
        const inputs = form.querySelectorAll('input:not([type="file"]), select, textarea');

        inputs.forEach(input => {
            const name = input.name;
            
            if (!name || name === 'category') return;
            if (input.type === 'hidden' && name.startsWith('csrf_')) return;

            // Handle different input types
            if (input.type === 'checkbox') {
                if (input.checked) {
                    formData.append(name, input.value || 'on');
                }
            } else if (input.type === 'radio') {
                if (input.checked) {
                    formData.append(name, input.value);
                }
            } else {
                const trimmed = input.value.trim();
                if (trimmed) {
                    formData.append(name, trimmed);
                }
            }
        });
    }

    /**
     * Append files
     */
    _appendFiles(formData, category) {
        const files = FileManager.getFiles(category);
        files.forEach((file) => {
            formData.append('attachments[]', file, file.name);
        });
    }

    /**
     * Log FormData contents
     */
    _logFormData(formData) {
        Logger.group('FormData Summary');
        
        let fieldCount = 0;
        let fileCount = 0;
        
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                Logger.log(`${key}: [File] ${value.name} (${(value.size / 1024).toFixed(2)} KB)`);
                fileCount++;
            } else {
                Logger.log(`${key}:`, value);
                fieldCount++;
            }
        }
        
        Logger.log(`Total: ${fieldCount} fields, ${fileCount} files`);
        Logger.groupEnd();
    }
}

export default FormDataCollector;