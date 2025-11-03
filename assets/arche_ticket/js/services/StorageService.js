class StorageService {
    constructor() {
        this.fileDataMap = new Map();
        this.validationRules = new Map();
    }

    // File storage
    addFiles(formId, files) {
        const current = this.fileDataMap.get(formId) || [];
        this.fileDataMap.set(formId, [...current, ...files]);
        return this.fileDataMap.get(formId);
    }

    removeFile(formId, fileName) {
        const files = this.fileDataMap.get(formId) || [];
        const filtered = files.filter(f => f.name !== fileName);
        this.fileDataMap.set(formId, filtered);
        return filtered;
    }

    getFiles(formId) {
        return this.fileDataMap.get(formId) || [];
    }

    clearFiles(formId) {
        this.fileDataMap.delete(formId);
    }

    hasFile(formId, fileName) {
        const files = this.getFiles(formId);
        return files.some(f => f.name === fileName);
    }

    getFileCount(formId) {
        return this.getFiles(formId).length;
    }

    // Validation rules
    setValidationRule(formId, fieldName, rule) {
        if (!this.validationRules.has(formId)) {
            this.validationRules.set(formId, new Map());
        }
        this.validationRules.get(formId).set(fieldName, rule);
    }

    getValidationRule(formId, fieldName) {
        return this.validationRules.get(formId)?.get(fieldName);
    }

    clearValidationRules(formId) {
        this.validationRules.delete(formId);
    }
}

export default new StorageService();