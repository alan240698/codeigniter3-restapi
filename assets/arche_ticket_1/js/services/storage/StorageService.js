import Logger                   from '../../utils/logger.js';
import EventBus                 from '../../core/EventBus.js';
import FileStorageManager       from './FileStorageManager.js';
import ValidationRuleManager    from './ValidationRuleManager.js';
import KeyValueStorage          from './KeyValueStorage.js';
import LocalStoragePersistence  from './LocalStoragePersistence.js';
import StorageStatsCalculator   from './StorageStatsCalculator.js';

class StorageService {
    constructor() {
        this.fileStorage = new FileStorageManager();
        this.validationRules = new ValidationRuleManager();
        this.keyValueStorage = new KeyValueStorage();
        this.persistence = new LocalStoragePersistence('ticket_system_storage');
        this.statsCalculator = new StorageStatsCalculator(this.fileStorage);

        this._initPersistence();
    }

    _initPersistence() {
        this.persistence.restore(this.validationRules);
    }

    // ========================================
    // File Storage Methods
    // ========================================

    addFiles(formId, files) {
        return this.fileStorage.addFiles(formId, files);
    }

    removeFile(formId, fileName) {
        return this.fileStorage.removeFile(formId, fileName);
    }

    getFiles(formId) {
        return this.fileStorage.getFiles(formId);
    }

    clearFiles(formId) {
        return this.fileStorage.clearFiles(formId);
    }

    hasFile(formId, fileName) {
        return this.fileStorage.hasFile(formId, fileName);
    }

    getFileCount(formId) {
        return this.fileStorage.getFileCount(formId);
    }

    getFileSize(formId) {
        return this.fileStorage.getFileSize(formId);
    }

    getFile(formId, fileName) {
        return this.fileStorage.getFile(formId, fileName);
    }

    clearAllFiles() {
        return this.fileStorage.clearAll();
    }

    // ========================================
    // Validation Rules Methods
    // ========================================

    setValidationRule(formId, fieldName, rule) {
        this.validationRules.set(formId, fieldName, rule);
        this.persistence.persist(this.validationRules);
    }

    getValidationRule(formId, fieldName) {
        return this.validationRules.get(formId, fieldName);
    }

    clearValidationRules(formId) {
        this.validationRules.clear(formId);
        this.persistence.persist(this.validationRules);
    }

    clearAllValidationRules() {
        this.validationRules.clearAll();
        this.persistence.persist(this.validationRules);
    }

    // ========================================
    // Key-Value Storage Methods
    // ========================================

    set(key, value) {
        this.keyValueStorage.set(key, value);
        this.persistence.persist(this.validationRules, this.keyValueStorage);
    }

    get(key, defaultValue = null) {
        return this.keyValueStorage.get(key, defaultValue);
    }

    remove(key) {
        this.keyValueStorage.remove(key);
        this.persistence.persist(this.validationRules, this.keyValueStorage);
    }

    // ========================================
    // Utility Methods
    // ========================================

    getStats() {
        return this.statsCalculator.getStats();
    }

    clearAll() {
        this.fileStorage.clearAll();
        this.validationRules.clearAll();
        this.keyValueStorage.clearAll();
        
        Logger.info('Cleared all storage');
        EventBus.emit('storage:cleared');
    }
}

export default new StorageService();