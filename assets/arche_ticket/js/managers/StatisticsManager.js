import ApiService from '../services/ApiService.js';
import StateManager from '../core/StateManager.js';
import EventBus from '../core/EventBus.js';
import { $ } from '../utils/dom.js';

class StatisticsManager {
    async load() {
        try {
            const response = await ApiService.listTickets();
            
            if (response.success && response.data) {
                this.update(response.data);
            }
        } catch (error) {
            console.error('Failed to load statistics:', error);
        }
    }

    update(tickets) {
        const counts = {
            pending: 0,
            processing: 0,
            solved: 0,
            closed: 0
        };

        tickets.forEach(ticket => {
            const status = ticket.status.toLowerCase();
            if (counts.hasOwnProperty(status)) {
                counts[status]++;
            }
        });

        // Update DOM
        Object.keys(counts).forEach(status => {
            const el = $(`#${status}Count`);
            if (el) el.textContent = counts[status];
        });

        // Update state
        StateManager.updateStatistics(counts);

        // Emit event
        EventBus.emit('statistics:updated', counts);
    }
}

export default new StatisticsManager();