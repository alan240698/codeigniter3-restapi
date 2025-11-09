class UploadEventManager {
    constructor() {
        this.listeners = new Map();
    }

    add(element, event, handler) {
        if (!this.listeners.has(element)) {
            this.listeners.set(element, []);
        }
        
        this.listeners.get(element).push({ event, handler });
        element.addEventListener(event, handler);
    }

    remove(element, event, handler) {
        element.removeEventListener(event, handler);
        
        const listeners = this.listeners.get(element);
        if (listeners) {
            const index = listeners.findIndex(
                l => l.event === event && l.handler === handler
            );
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }

    cleanup() {
        this.listeners.forEach((listeners, element) => {
            listeners.forEach(({ event, handler }) => {
                element.removeEventListener(event, handler);
            });
        });
        this.listeners.clear();
    }

    removeElement(element) {
        const listeners = this.listeners.get(element);
        if (listeners) {
            listeners.forEach(({ event, handler }) => {
                element.removeEventListener(event, handler);
            });
            this.listeners.delete(element);
        }
    }
}

export default UploadEventManager;