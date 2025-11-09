import EventBus from './EventBus.js';

class StateManager {
    constructor() {
        this.initialState = {
            activeCard: null,
            selectedCategory: null,
            isModalOpen: false,
            statistics: {
                new: 0,
                pending: 0,
                processing: 0,
                solved: 0,
                closed: 0
            }
        };
        this.state = JSON.parse(JSON.stringify(this.initialState));
    }

    get(key) {
        if (key) {
            // Return deep copy to prevent external mutations
            return JSON.parse(JSON.stringify(this.state[key]));
        }
        return JSON.parse(JSON.stringify(this.state));
    }

    set(key, value) {
        if (!this.state.hasOwnProperty(key)) {
            console.warn(`State key "${key}" does not exist`);
            return;
        }

        const oldValue = this.state[key];
        
        // Deep copy to prevent mutations
        this.state[key] = JSON.parse(JSON.stringify(value));
        
        EventBus.emit('state:change', { key, value, oldValue });
        EventBus.emit(`state:${key}`, value);
    }

    update(updates) {
        if (!updates || typeof updates !== 'object') {
            console.warn('Invalid state updates:', updates);
            return;
        }

        Object.entries(updates).forEach(([key, value]) => {
            this.set(key, value);
        });
    }

    updateStatistics(stats) {
        if (!stats || typeof stats !== 'object') {
            console.warn('Invalid statistics update:', stats);
            return;
        }

        const validKeys = Object.keys(this.state.statistics);
        const validStats = Object.keys(stats)
            .filter(key => validKeys.includes(key))
            .reduce((obj, key) => {
                obj[key] = stats[key];
                return obj;
            }, {});

        this.set('statistics', {
            ...this.state.statistics,
            ...validStats
        });
    }

    reset(key) {
        if (key) {
            this.set(key, JSON.parse(JSON.stringify(this.initialState[key])));
        } else {
            this.state = JSON.parse(JSON.stringify(this.initialState));
            EventBus.emit('state:reset');
        }
    }

    subscribe(key, callback) {
        return EventBus.on(`state:${key}`, callback);
    }
}

export default new StateManager();