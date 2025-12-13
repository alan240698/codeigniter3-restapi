import Logger from '../../utils/logger.js';
import { $ } from '../../utils/dom.js';

class StatisticsDOMUpdater {
    constructor(validStatuses) {
        this.validStatuses = validStatuses;
    }

    /**
     * Update DOM elements with counts
     * @param {Object} counts - Statistics counts
     */
    updateDOM(counts) {
        this.validStatuses.forEach(status => {
            this._updateStatusElement(status, counts[status] || 0);
        });
    }

    /**
     * Update single status element
     * @private
     */
    _updateStatusElement(status, count) {
        const elementId = `${status}Count`;
        const element = $(elementId) || $(`#${elementId}`);
        
        if (element) {
            const currentValue = parseInt(element.textContent) || 0;
            
            // Animate number change if different
            if (currentValue !== count) {
                this._animateCount(element, currentValue, count);
            }
        } else {
            Logger.warn(`Statistics element not found: ${elementId}`);
        }
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
            this._addScaleAnimation(element);
            animate();
        }
    }

    /**
     * Add scale animation to element
     * @private
     */
    _addScaleAnimation(element) {
        element.style.transition = 'transform 0.2s ease';
        element.style.transform = 'scale(1.1)';
        
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 200);
    }

    /**
     * Get current counts from DOM
     * @returns {Object} Current counts
     */
    getCurrentCountsFromDOM() {
        const counts = {};
        
        this.validStatuses.forEach(status => {
            const element = $(`${status}Count`) || $(`#${status}Count`);
            counts[status] = element ? parseInt(element.textContent) || 0 : 0;
        });

        return counts;
    }
}

export default StatisticsDOMUpdater;