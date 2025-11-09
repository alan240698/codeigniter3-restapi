import Logger from '../../utils/logger.js';

class ValidationRuleManager {
    constructor() {
        this.rules = new Map();
    }

    set(formId, fieldName, rule) {
        this._validate(formId, fieldName);

        if (!this.rules.has(formId)) {
            this.rules.set(formId, new Map());
        }
        
        this.rules.get(formId).set(fieldName, rule);
        
        Logger.debug(`Set validation rule for ${formId}.${fieldName}`, rule);
    }

    get(formId, fieldName) {
        if (!formId || !fieldName) {
            return undefined;
        }
        return this.rules.get(formId)?.get(fieldName);
    }

    clear(formId) {
        if (!formId) {
            throw new Error('formId is required');
        }

        const hasRules = this.rules.has(formId);
        this.rules.delete(formId);
        
        if (hasRules) {
            Logger.info(`Cleared validation rules for ${formId}`);
        }
    }

    clearAll() {
        const formCount = this.rules.size;
        this.rules.clear();
        
        Logger.info(`Cleared validation rules from ${formCount} form(s)`);
    }

    toArray() {
        return Array.from(this.rules.entries());
    }

    fromArray(data) {
        this.rules.clear();
        if (Array.isArray(data)) {
            data.forEach(([formId, rules]) => {
                this.rules.set(formId, new Map(rules));
            });
        }
    }

    _validate(formId, fieldName) {
        if (!formId || !fieldName) {
            throw new Error('formId and fieldName are required');
        }
    }
}

export default ValidationRuleManager;