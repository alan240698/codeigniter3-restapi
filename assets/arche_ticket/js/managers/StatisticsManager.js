import ApiService from '../services/ApiService.js';
import StateManager from '../core/StateManager.js';
import EventBus from '../core/EventBus.js';
import Logger from '../utils/logger.js';
import { $ } from '../utils/dom.js';

class StatisticsManager {
    constructor() {
        this.validStatuses = ['new', 'pending', 'processing', 'solved', 'closed'];
        this.isLoading = false;
    }

    /**
     * Load statistics from API
     * @returns {Promise<boolean>} Success status
     */
    async load() {
        if (this.isLoading) {
            Logger.warn('Statistics already loading');
            return false;
        }

        this.isLoading = true;

        try {
            const response = await ApiService.listTickets();
            
            // Validate response structure
            if (!response || typeof response !== 'object') {
                throw new Error('Invalid response format');
            }

            if (!response.success) {
                throw new Error(response.message || 'Failed to load tickets');
            }

            if (!Array.isArray(response.data)) {
                throw new Error('Invalid data format: expected array');
            }

            this.update(response.data);
            return true;

        } catch (error) {
            Logger.error('Failed to load statistics:', error);
            
            // Emit error event
            EventBus.emit('statistics:error', { error });
            
            return false;
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Update statistics from tickets data
     * @param {Array} tickets - Array of ticket objects
     */
    update(tickets) {
        if (!Array.isArray(tickets)) {
            Logger.error('Invalid tickets data: expected array', { tickets });
            return;
        }

        // Initialize counts
        const counts = this.validStatuses.reduce((acc, status) => {
            acc[status] = 0;
            return acc;
        }, {});

        // Count by status
        let unknownStatuses = new Set();

        tickets.forEach(ticket => {
            if (!ticket || typeof ticket !== 'object') {
                Logger.warn('Invalid ticket object', { ticket });
                return;
            }

            const status = ticket.status?.toLowerCase();
            
            if (!status) {
                Logger.warn('Ticket missing status', { ticket });
                return;
            }

            if (counts.hasOwnProperty(status)) {
                counts[status]++;
            } else {
                unknownStatuses.add(ticket.status);
            }
        });

        // Log unknown statuses
        if (unknownStatuses.size > 0) {
            Logger.warn('Unknown ticket statuses found:', Array.from(unknownStatuses));
        }

        // Update DOM elements
        this._updateDOM(counts);

        // Update state manager
        try {
            StateManager.updateStatistics(counts);
        } catch (error) {
            Logger.error('Failed to update StateManager:', error);
        }

        // Emit event for other components
        EventBus.emit('statistics:updated', { 
            counts, 
            total: tickets.length,
            unknownStatuses: Array.from(unknownStatuses)
        });

        Logger.info('Statistics updated', counts);
    }

    /**
     * Update DOM elements with counts
     * @private
     */
    _updateDOM(counts) {
        this.validStatuses.forEach(status => {
            const elementId = `${status}Count`;
            const element = $(elementId) || $(`#${elementId}`);
            
            if (element) {
                const count = counts[status] || 0;
                
                // Animate number change if different
                if (element.textContent !== String(count)) {
                    this._animateCount(element, parseInt(element.textContent) || 0, count);
                }
            } else {
                Logger.warn(`Statistics element not found: ${elementId}`);
            }
        });
    }

    /**
     * Animate count change with smooth transition
     * @private
     */
    _animateCount(element, from, to) {
        const duration = 500; // ms
        const steps = 20;
        const increment = (to - from) / steps;
        const stepDuration = duration / steps;
        
        let current = from;
        let step = 0;

        const animate = () => {
            if (step >= steps) {
                element.textContent = to;
                return;
            }

            current += increment;
            element.textContent = Math.round(current);
            step++;

            setTimeout(animate, stepDuration);
        };

        // Start animation if numbers are different
        if (from !== to) {
            element.style.transition = 'transform 0.2s ease';
            element.style.transform = 'scale(1.1)';
            
            setTimeout(() => {
                element.style.transform = 'scale(1)';
            }, 200);

            animate();
        }
    }

    /**
     * Refresh statistics
     * @returns {Promise<boolean>}
     */
    async refresh() {
        Logger.info('Refreshing statistics...');
        return await this.load();
    }

    /**
     * Get current statistics
     * @returns {Object} Current counts
     */
    getCurrentStats() {
        const counts = {};
        
        this.validStatuses.forEach(status => {
            const element = $(`${status}Count`) || $(`#${status}Count`);
            counts[status] = element ? parseInt(element.textContent) || 0 : 0;
        });

        return counts;
    }
}

export default new StatisticsManager();