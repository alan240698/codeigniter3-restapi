import StatisticsConfig from './StatisticsConfig.js';
import StatisticsLoader from './StatisticsLoader.js';
import StatisticsDataProcessor from './StatisticsDataProcessor.js';
import StatisticsDOMUpdater from './StatisticsDOMUpdater.js';
import StatisticsStateManager from './StatisticsStateManager.js';
import Logger from '../../utils/logger.js';

class StatisticsManager {
    constructor() {
        this.validStatuses = StatisticsConfig.getValidStatuses();
        this.loader = new StatisticsLoader();
        this.dataProcessor = new StatisticsDataProcessor(this.validStatuses);
        this.domUpdater = new StatisticsDOMUpdater(this.validStatuses);
        this.stateManager = new StatisticsStateManager();
    }

    /**
     * Load statistics from API
     * @returns {Promise<boolean>} Success status
     */
    async load() {
        if (this.loader.isCurrentlyLoading()) {
            Logger.warn('Statistics already loading');
            return false;
        }

        this.stateManager.emitLoadingState(true);

        try {
            const response = await this.loader.load();
            
            // Validate response structure
            const validation = this.dataProcessor.validateResponse(response);
            if (!validation.valid) {
                throw new Error(validation.error);
            }

            // Process and update
            this.update(response.data);
            
            this.stateManager.emitLoadingState(false);
            return true;

        } catch (error) {
            Logger.error('Failed to load statistics:', error);
            this.stateManager.emitError(error);
            this.stateManager.emitLoadingState(false);
            return false;
        }
    }

    /**
     * Update statistics from tickets data
     * @param {Array} tickets - Array of ticket objects
     */
    update(tickets) {
        // Process data
        const processedData = this.dataProcessor.process(tickets);

        // Update DOM
        this.domUpdater.updateDOM(processedData.counts);

        // Update state and emit events
        this.stateManager.updateState(processedData);
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
     * Get current statistics from DOM
     * @returns {Object} Current counts
     */
    getCurrentStats() {
        return this.domUpdater.getCurrentCountsFromDOM();
    }

    /**
     * Check if currently loading
     * @returns {boolean}
     */
    isLoading() {
        return this.loader.isCurrentlyLoading();
    }

    /**
     * Get valid statuses
     * @returns {Array<string>}
     */
    getValidStatuses() {
        return [...this.validStatuses];
    }
}

export default new StatisticsManager();