class ValidationMessageManager {
    constructor() {
        this.messages = {
            required: '{label} is required',
            email: 'Invalid email',
            phone: 'Invalid phone number',
            url: 'Invalid URL',
            minLength: 'Minimum length is {min} characters',
            maxLength: 'Maximum length is {max} characters',
            min: 'Minimum value is {min}',
            max: 'The maximum value is {max}',
            pattern: '{label} is not in the correct format',
            alphanumeric: '{label} can only contain letters and numbers',
            numeric: '{label} can only contain numbers',
            alpha: '{label} must contain only letters'
        };
    }

    set(key, message) {
        this.messages[key] = message;
    }

    get(key, params = {}) {
        let message = this.messages[key] || key;

        Object.keys(params).forEach(paramKey => {
            message = message.replace(`{${paramKey}}`, params[paramKey]);
        });

        return message;
    }

    has(key) {
        return key in this.messages;
    }

    remove(key) {
        delete this.messages[key];
    }
}

export default ValidationMessageManager;