import Logger from '../../utils/logger.js';

class ValidationPatternManager {
    constructor() {
        this.patterns = {
            email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            phone: /^[\d\s\-\+\(\)]+$/,
            url: /^https?:\/\/.+/,
            alphanumeric: /^[a-zA-Z0-9]+$/,
            numeric: /^\d+$/,
            alpha: /^[a-zA-Z]+$/
        };
    }

    add(name, pattern) {
        if (!(pattern instanceof RegExp)) {
            throw new Error('Pattern must be a RegExp');
        }

        this.patterns[name] = pattern;
        Logger.debug(`Added pattern: ${name}`);
    }

    get(name) {
        return this.patterns[name];
    }

    has(name) {
        return name in this.patterns;
    }

    remove(name) {
        delete this.patterns[name];
        Logger.debug(`Removed pattern: ${name}`);
    }
}

export default ValidationPatternManager;