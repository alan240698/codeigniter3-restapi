import Logger from '../../utils/logger.js';
import EventBus from '../../core/EventBus.js';

class FileStorageManager {
    constructor() {
        this.fileDataMap = new Map();
        this.maxStorageSize = 50 * 1024 * 1024; // 50MB
    }

    addFiles(formId, files) {
        this._validateFormId(formId);
        this._validateFilesArray(files);
        this._checkStorageLimit(files);

        const current = this.fileDataMap.get(formId) || [];
        const updated = [...current, ...files];
        
        this.fileDataMap.set(formId, updated);
        
        const newFilesSize = files.reduce((sum, file) => sum + file.size, 0);
        Logger.info(`Added ${files.length} file(s) to ${formId}`, {
            total: updated.length,
            size: this._formatBytes(newFilesSize)
        });

        EventBus.emit('storage:files:added', { 
            formId, 
            files, 
            total: updated.length 
        });

        return updated;
    }

    removeFile(formId, fileName) {
        this._validateFormId(formId);
        this._validateFileName(fileName);

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

        EventBus.emit('storage:file:removed', { 
            formId, 
            fileName, 
            remaining: filtered.length 
        });

        return filtered;
    }

    getFiles(formId) {
        if (!formId) {
            Logger.warn('getFiles called without formId');
            return [];
        }
        return this.fileDataMap.get(formId) || [];
    }

    clearFiles(formId) {
        this._validateFormId(formId);

        const files = this.fileDataMap.get(formId) || [];
        const count = files.length;

        this.fileDataMap.delete(formId);
        
        Logger.info(`Cleared ${count} file(s) from ${formId}`);
        EventBus.emit('storage:files:cleared', { formId, count });
    }

    hasFile(formId, fileName) {
        if (!formId || !fileName) return false;
        const files = this.getFiles(formId);
        return files.some(f => f.name === fileName);
    }

    getFileCount(formId) {
        return this.getFiles(formId).length;
    }

    getFileSize(formId) {
        const files = this.getFiles(formId);
        return files.reduce((sum, file) => sum + file.size, 0);
    }

    getFile(formId, fileName) {
        const files = this.getFiles(formId);
        return files.find(f => f.name === fileName) || null;
    }

    clearAll() {
        const formCount = this.fileDataMap.size;
        this.fileDataMap.clear();
        
        Logger.info(`Cleared all files from ${formCount} form(s)`);
        EventBus.emit('storage:all-files:cleared', { formCount });
    }

    getTotalSize() {
        let totalSize = 0;
        for (const files of this.fileDataMap.values()) {
            totalSize += files.reduce((sum, file) => sum + file.size, 0);
        }
        return totalSize;
    }

    _validateFormId(formId) {
        if (!formId) {
            throw new Error('formId is required');
        }
    }

    _validateFileName(fileName) {
        if (!fileName) {
            throw new Error('fileName is required');
        }
    }

    _validateFilesArray(files) {
        if (!Array.isArray(files)) {
            throw new Error('files must be an array');
        }
    }

    _checkStorageLimit(newFiles) {
        const totalSize = this.getTotalSize();
        const newFilesSize = newFiles.reduce((sum, file) => sum + file.size, 0);
        
        if (totalSize + newFilesSize > this.maxStorageSize) {
            throw new Error('Storage limit exceeded. Please remove some files.');
        }
    }

    _formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }
}

export default FileStorageManager;
