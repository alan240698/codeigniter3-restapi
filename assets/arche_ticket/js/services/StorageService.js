import Logger from '../utils/logger.js';
import EventBus from '../core/EventBus.js';

class StorageService {
    constructor() {
        this.fileDataMap = new Map();
        this.validationRules = new Map();
        this.storageKey = 'ticket_system_storage';
        this.maxStorageSize = 50 * 1024 * 1024; // 50MB limit for in-memory storage
        
        this._initPersistence();
    }

    /**
     * Initialize persistence from localStorage if available
     * @private
     */
    _initPersistence() {
        try {
            const stored = localStorage.getItem(this.storageKey);
            if (stored) {
                const data = JSON.parse(stored);
                Logger.info('Restored storage from localStorage', data);
            }
        } catch (error) {
            Logger.warn('Failed to restore from localStorage:', error);
        }
    }

    // ========================================
    // File Storage Methods
    // ========================================

    /**
     * Add files to storage
     * @param {string} formId - Form identifier
     * @param {File[]} files - Array of File objects
     * @returns {File[]} Updated files array
     */
    addFiles(formId, files) {
        if (!formId) {
            throw new Error('formId is required');
        }

        if (!Array.isArray(files)) {
            throw new Error('files must be an array');
        }

        // Validate storage size
        const totalSize = this._calculateTotalSize();
        const newFilesSize = files.reduce((sum, file) => sum + file.size, 0);
        
        if (totalSize + newFilesSize > this.maxStorageSize) {
            throw new Error('Storage limit exceeded. Please remove some files.');
        }

        const current = this.fileDataMap.get(formId) || [];
        const updated = [...current, ...files];
        
        this.fileDataMap.set(formId, updated);
        
        Logger.info(`Added ${files.length} file(s) to ${formId}`, {
            total: updated.length,
            size: this._formatBytes(newFilesSize)
        });

        // Emit event
        EventBus.emit('storage:files:added', { formId, files, total: updated.length });

        return updated;
    }

    /**
     * Remove file from storage
     * @param {string} formId - Form identifier
     * @param {string} fileName - File name to remove
     * @returns {File[]} Updated files array
     */
    removeFile(formId, fileName) {
        if (!formId || !fileName) {
            throw new Error('formId and fileName are required');
        }

        const files = this.fileDataMap.get(formId) || [];
        const filtered = files.filter(f => f.name !== fileName);
        
        if (filtered.length === files.length) {
            Logger.warn(`File "${fileName}" not found in ${formId}`);
            return files;
        }

        this.fileDataMap.set(formId, filtered);
        
        Logger.info(`Removed file "${fileName}" from ${formId}`, {
            remaining: filtered.length
        });

        // Emit event
        EventBus.emit('storage:file:removed', { formId, fileName, remaining: filtered.length });

        return filtered;
    }

    /**
     * Get files for a form
     * @param {string} formId - Form identifier
     * @returns {File[]} Array of files
     */
    getFiles(formId) {
        if (!formId) {
            Logger.warn('getFiles called without formId');
            return [];
        }

        return this.fileDataMap.get(formId) || [];
    }

    /**
     * Clear all files for a form
     * @param {string} formId - Form identifier
     */
    clearFiles(formId) {
        if (!formId) {
            throw new Error('formId is required');
        }

        const files = this.fileDataMap.get(formId) || [];
        const count = files.length;

        this.fileDataMap.delete(formId);
        
        Logger.info(`Cleared ${count} file(s) from ${formId}`);

        // Emit event
        EventBus.emit('storage:files:cleared', { formId, count });
    }

    /**
     * Check if file exists in storage
     * @param {string} formId - Form identifier
     * @param {string} fileName - File name to check
     * @returns {boolean}
     */
    hasFile(formId, fileName) {
        if (!formId || !fileName) {
            return false;
        }

        const files = this.getFiles(formId);
        return files.some(f => f.name === fileName);
    }

    /**
     * Get file count for a form
     * @param {string} formId - Form identifier
     * @returns {number} Number of files
     */
    getFileCount(formId) {
        return this.getFiles(formId).length;
    }

    /**
     * Get total size of files for a form
     * @param {string} formId - Form identifier
     * @returns {number} Total size in bytes
     */
    getFileSize(formId) {
        const files = this.getFiles(formId);
        return files.reduce((sum, file) => sum + file.size, 0);
    }

    /**
     * Get file by name
     * @param {string} formId - Form identifier
     * @param {string} fileName - File name
     * @returns {File|null}
     */
    getFile(formId, fileName) {
        const files = this.getFiles(formId);
        return files.find(f => f.name === fileName) || null;
    }

    /**
     * Clear all files from all forms
     */
    clearAllFiles() {
        const formCount = this.fileDataMap.size;
        this.fileDataMap.clear();
        
        Logger.info(`Cleared all files from ${formCount} form(s)`);
        EventBus.emit('storage:all-files:cleared', { formCount });
    }

    // ========================================
    // Validation Rules Methods
    // ========================================

    /**
     * Set validation rule for a field
     * @param {string} formId - Form identifier
     * @param {string} fieldName - Field name
     * @param {Object} rule - Validation rule
     */
    setValidationRule(formId, fieldName, rule) {
        if (!formId || !fieldName) {
            throw new Error('formId and fieldName are required');
        }

        if (!this.validationRules.has(formId)) {
            this.validationRules.set(formId, new Map());
        }
        
        this.validationRules.get(formId).set(fieldName, rule);
        
        Logger.debug(`Set validation rule for ${formId}.${fieldName}`, rule);
    }

    /**
     * Get validation rule for a field
     * @param {string} formId - Form identifier
     * @param {string} fieldName - Field name
     * @returns {Object|undefined}
     */
    getValidationRule(formId, fieldName) {
        if (!formId || !fieldName) {
            return undefined;
        }

        return this.validationRules.get(formId)?.get(fieldName);
    }

    /**
     * Clear validation rules for a form
     * @param {string} formId - Form identifier
     */
    clearValidationRules(formId) {
        if (!formId) {
            throw new Error('formId is required');
        }

        const hasRules = this.validationRules.has(formId);
        this.validationRules.delete(formId);
        
        if (hasRules) {
            Logger.info(`Cleared validation rules for ${formId}`);
        }
    }

    /**
     * Clear all validation rules
     */
    clearAllValidationRules() {
        const formCount = this.validationRules.size;
        this.validationRules.clear();
        
        Logger.info(`Cleared validation rules from ${formCount} form(s)`);
    }

    // ========================================
    // Generic Key-Value Storage
    // ========================================

    /**
     * Set custom data
     * @param {string} key - Storage key
     * @param {any} value - Value to store
     */
    set(key, value) {
        if (!key) {
            throw new Error('key is required');
        }

        // Store in memory
        this[`_custom_${key}`] = value;

        // Try to persist to localStorage
        this._persistToLocalStorage();

        Logger.debug(`Set storage key: ${key}`);
    }

    /**
     * Get custom data
     * @param {string} key - Storage key
     * @param {any} defaultValue - Default value if not found
     * @returns {any}
     */
    get(key, defaultValue = null) {
        if (!key) {
            return defaultValue;
        }

        const value = this[`_custom_${key}`];
        return value !== undefined ? value : defaultValue;
    }

    /**
     * Remove custom data
     * @param {string} key - Storage key
     */
    remove(key) {
        if (!key) {
            throw new Error('key is required');
        }

        delete this[`_custom_${key}`];
        this._persistToLocalStorage();

        Logger.debug(`Removed storage key: ${key}`);
    }

    // ========================================
    // Utility Methods
    // ========================================

    /**
     * Calculate total storage size
     * @private
     */
    _calculateTotalSize() {
        let totalSize = 0;
        
        for (const files of this.fileDataMap.values()) {
            totalSize += files.reduce((sum, file) => sum + file.size, 0);
        }

        return totalSize;
    }

    /**
     * Format bytes to human readable
     * @private
     */
    _formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * Persist non-file data to localStorage
     * @private
     */
    _persistToLocalStorage() {
        try {
            const data = {
                timestamp: Date.now(),
                validationRules: Array.from(this.validationRules.entries())
            };

            localStorage.setItem(this.storageKey, JSON.stringify(data));
        } catch (error) {
            Logger.warn('Failed to persist to localStorage:', error);
        }
    }

    /**
     * Get storage statistics
     * @returns {Object}
     */
    getStats() {
        const totalSize = this._calculateTotalSize();
        const formCount = this.fileDataMap.size;
        let totalFiles = 0;

        for (const files of this.fileDataMap.values()) {
            totalFiles += files.length;
        }

        return {
            formCount,
            totalFiles,
            totalSize,
            totalSizeFormatted: this._formatBytes(totalSize),
            maxSize: this.maxStorageSize,
            maxSizeFormatted: this._formatBytes(this.maxStorageSize),
            usagePercent: Math.round((totalSize / this.maxStorageSize) * 100)
        };
    }

    /**
     * Clear all storage
     */
    clearAll() {
        this.clearAllFiles();
        this.clearAllValidationRules();
        
        // Clear custom keys
        Object.keys(this).forEach(key => {
            if (key.startsWith('_custom_')) {
                delete this[key];
            }
        });

        Logger.info('Cleared all storage');
        EventBus.emit('storage:cleared');
    }
}

export default new StorageService();