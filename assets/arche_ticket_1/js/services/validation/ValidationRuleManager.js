import Logger from '../../utils/logger.js';

class ValidationRuleManager {
    constructor() {
        this.rules = new Map();
    }

    add(fieldName, validator) {
        if (!fieldName || typeof validator !== 'function') {
            throw new Error('Invalid rule parameters');
        }

        this.rules.set(fieldName, validator);
        Logger.debug(`Added custom rule for ${fieldName}`);
    }

    remove(fieldName) {
        this.rules.delete(fieldName);
        Logger.debug(`Removed custom rule for ${fieldName}`);
    }

    get(fieldName) {
        return this.rules.get(fieldName);
    }

    has(fieldName) {
        return this.rules.has(fieldName);
    }

    clear() {
        this.rules.clear();
        Logger.debug('Cleared all custom rules');
    }
}

export default ValidationRuleManager;