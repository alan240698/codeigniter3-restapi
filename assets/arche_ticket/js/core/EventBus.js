class EventBus {
    constructor() {
        this.events = {};
    }

    on(event, callback) {
        if (!this.events[event]) {
            this.events[event] = [];
        }
        this.events[event].push(callback);
        return () => this.off(event, callback);
    }

    off(event, callback) {
        if (!this.events[event]) return;
        this.events[event] = this.events[event].filter(cb => cb !== callback);
        
        // Cleanup empty arrays
        if (this.events[event].length === 0) {
            delete this.events[event];
        }
    }

    emit(event, data) {
        if (!this.events[event]) return;
        
        // Clone array to prevent issues if listeners modify the array
        const listeners = [...this.events[event]];
        listeners.forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error(`Error in event listener for ${event}:`, error);
            }
        });
    }

    once(event, callback) {
        const onceWrapper = (data) => {
            callback(data);
            this.off(event, onceWrapper);
        };
        this.on(event, onceWrapper);
    }

    clear(event) {
        if (event) {
            delete this.events[event];
        } else {
            this.events = {};
        }
    }

    getListeners(event) {
        if (event) {
            return (this.events[event] || []).length;
        }
        return Object.keys(this.events).reduce((total, key) => {
            return total + this.events[key].length;
        }, 0);
    }
}

export default new EventBus();