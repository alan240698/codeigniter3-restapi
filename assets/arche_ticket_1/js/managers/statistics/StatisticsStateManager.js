import StateManager from '../../core/StateManager.js';
import EventBus from '../../core/EventBus.js';
import Logger from '../../utils/logger.js';

class StatisticsStateManager {
    /**
     * Update state with new statistics
     * @param {Object} data - Statistics data
     */
    updateState(data) {
        const { counts, total, unknownStatuses } = data;

        // Update state manager
        try {
            StateManager.updateStatistics(counts);
        } catch (error) {
            Logger.error('Failed to update StateManager:', error);
        }

        // Emit event for other components
        EventBus.emit('statistics:updated', { 
            counts, 
            total,
            unknownStatuses
        });

        Logger.info('Statistics updated', counts);
    }

    /**
     * Emit error event
     * @param {Error} error - Error object
     */
    emitError(error) {
        EventBus.emit('statistics:error', { error });
    }

    /**
     * Emit loading event
     * @param {boolean} isLoading - Loading state
     */
    emitLoadingState(isLoading) {
        EventBus.emit('statistics:loading', { isLoading });
    }
}

export default StatisticsStateManager;