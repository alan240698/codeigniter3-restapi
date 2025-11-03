class Logger {
    constructor() {
        this.enabled = true;
    }

    log(...args) {
        if (this.enabled) console.log(...args);
    }

    error(...args) {
        if (this.enabled) console.error(...args);
    }

    warn(...args) {
        if (this.enabled) console.warn(...args);
    }

    group(label) {
        if (this.enabled) console.group(label);
    }

    groupEnd() {
        if (this.enabled) console.groupEnd();
    }

    table(data) {
        if (this.enabled) console.table(data);
    }

    enable() {
        this.enabled = true;
    }

    disable() {
        this.enabled = false;
    }
}

export default new Logger();