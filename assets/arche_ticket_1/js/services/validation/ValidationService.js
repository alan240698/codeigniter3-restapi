import FieldValidator           from './FieldValidator.js';
import FormValidator            from './FormValidator.js';
import FileValidator            from './FileValidator.js';
import ValidationRuleManager    from './ValidationRuleManager.js';
import ValidationPatternManager from './ValidationPatternManager.js';
import ValidationMessageManager from './ValidationMessageManager.js';

class ValidationService {
    constructor() {
        this.ruleManager = new ValidationRuleManager();
        this.patternManager = new ValidationPatternManager();
        this.messageManager = new ValidationMessageManager();

        this.fieldValidator = new FieldValidator(
            this.ruleManager,
            this.patternManager,
            this.messageManager
        );

        this.formValidator = new FormValidator(this.fieldValidator);
        this.fileValidator = new FileValidator();
    }

    // ========================================
    // Field Validation
    // ========================================

    validateField(field) {
        return this.fieldValidator.validate(field);
    }

    // ========================================
    // Form Validation
    // ========================================

    validateForm(form) {
        return this.formValidator.validate(form);
    }

    // ========================================
    // File Validation
    // ========================================

    validateFile(file) {
        return this.fileValidator.validateSingle(file);
    }

    validateFiles(files, maxFiles) {
        return this.fileValidator.validateMultiple(files, maxFiles);
    }

    // ========================================
    // Rule Management
    // ========================================

    addRule(fieldName, validator) {
        return this.ruleManager.add(fieldName, validator);
    }

    removeRule(fieldName) {
        return this.ruleManager.remove(fieldName);
    }

    clearRules() {
        return this.ruleManager.clear();
    }

    // ========================================
    // Pattern Management
    // ========================================

    addPattern(name, pattern) {
        return this.patternManager.add(name, pattern);
    }

    getPattern(name) {
        return this.patternManager.get(name);
    }

    // ========================================
    // Message Management
    // ========================================

    setMessage(key, message) {
        return this.messageManager.set(key, message);
    }

    getMessage(key, params) {
        return this.messageManager.get(key, params);
    }
}

export default new ValidationService();