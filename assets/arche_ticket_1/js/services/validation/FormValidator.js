import Logger from '../../utils/logger.js';

class FormValidator {
    constructor(fieldValidator) {
        this.fieldValidator = fieldValidator;
    }

    validate(form) {
        if (!form) {
            Logger.error('validateForm: form is null');
            return { valid: false, errors: [] };
        }

        const errors = [];
        const fields = this._getValidatableFields(form);

        fields.forEach(field => {
            if (field.disabled) return;

            const result = this.fieldValidator.validate(field);
            if (!result.valid) {
                errors.push({ 
                    field, 
                    message: result.message,
                    name: field.name 
                });
            }
        });

        return {
            valid: errors.length === 0,
            errors
        };
    }

    _getValidatableFields(form) {
        return form.querySelectorAll(
            'input:not([type="hidden"]):not([type="file"]):not([type="button"]):not([type="submit"]), select, textarea'
        );
    }
}

export default FormValidator;