import ApiService from '../../services/api/index.js';
import Logger from '../../utils/logger.js';

class StatisticsLoader {
    constructor() {
        this.isLoading = false;
    }

    /**
     * Check if currently loading
     * @returns {boolean}
     */
    isCurrentlyLoading() {
        return this.isLoading;
    }

    /**
     * Load statistics from API
     * @returns {Promise<Object>} Response data
     */
    async load() {
        if (this.isLoading) {
            Logger.warn('Statistics already loading');
            throw new Error('Already loading');
        }

        this.isLoading = true;

        try {
            const response = await ApiService.listTickets();
            return response;
        } catch (error) {
            Logger.error('Failed to load statistics:', error);
            throw error;
        } finally {
            this.isLoading = false;
        }
    }
}

export default StatisticsLoader;
