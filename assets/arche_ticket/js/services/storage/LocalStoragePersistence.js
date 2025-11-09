import Logger from '../../utils/logger.js';

class LocalStoragePersistence {
    constructor(storageKey) {
        this.storageKey = storageKey;
    }

    restore(validationRuleManager) {
        try {
            const stored = localStorage.getItem(this.storageKey);
            if (stored) {
                const data = JSON.parse(stored);
                
                if (data.validationRules) {
                    validationRuleManager.fromArray(data.validationRules);
                }
                
                Logger.info('Restored storage from localStorage', data);
            }
        } catch (error) {
            Logger.warn('Failed to restore from localStorage:', error);
        }
    }

    persist(validationRuleManager, keyValueStorage = null) {
        try {
            const data = {
                timestamp: Date.now(),
                validationRules: validationRuleManager.toArray()
            };

            if (keyValueStorage) {
                data.customData = keyValueStorage.toObject();
            }

            localStorage.setItem(this.storageKey, JSON.stringify(data));
        } catch (error) {
            Logger.warn('Failed to persist to localStorage:', error);
        }
    }

    clear() {
        try {
            localStorage.removeItem(this.storageKey);
        } catch (error) {
            Logger.warn('Failed to clear localStorage:', error);
        }
    }
}

export default LocalStoragePersistence;
