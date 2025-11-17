import Logger from '../../utils/logger.js';

class KeyValueStorage {
    constructor() {
        this.data = new Map();
    }

    set(key, value) {
        if (!key) {
            throw new Error('key is required');
        }

        this.data.set(key, value);
        Logger.debug(`Set storage key: ${key}`);
    }

    get(key, defaultValue = null) {
        if (!key) {
            return defaultValue;
        }

        const value = this.data.get(key);
        return value !== undefined ? value : defaultValue;
    }

    remove(key) {
        if (!key) {
            throw new Error('key is required');
        }

        this.data.delete(key);
        Logger.debug(`Removed storage key: ${key}`);
    }

    has(key) {
        return this.data.has(key);
    }

    clearAll() {
        this.data.clear();
    }

    toObject() {
        return Object.fromEntries(this.data);
    }

    fromObject(obj) {
        this.data.clear();
        if (obj && typeof obj === 'object') {
            Object.entries(obj).forEach(([key, value]) => {
                this.data.set(key, value);
            });
        }
    }
}

export default KeyValueStorage;
