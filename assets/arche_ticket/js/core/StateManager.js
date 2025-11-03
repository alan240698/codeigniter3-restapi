import EventBus from './EventBus.js';

class StateManager {
    constructor() {
        this.state = {
            activeCard: null,
            selectedCategory: null,
            isModalOpen: false,
            statistics: {
                pending: 0,
                processing: 0,
                solved: 0,
                closed: 0
            }
        };
    }

    get(key) {
        return key ? this.state[key] : this.state;
    }

    set(key, value) {
        const oldValue = this.state[key];
        this.state[key] = value;
        
        EventBus.emit('state:change', { key, value, oldValue });
        EventBus.emit(`state:${key}`, value);
    }

    update(updates) {
        Object.entries(updates).forEach(([key, value]) => {
            this.set(key, value);
        });
    }

    updateStatistics(stats) {
        this.set('statistics', { ...this.state.statistics, ...stats });
    }
}

export default new StateManager();