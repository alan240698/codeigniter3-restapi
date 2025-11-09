import Logger from '../../utils/logger.js';

class StatisticsDataProcessor {
    constructor(validStatuses) {
        this.validStatuses = validStatuses;
    }

    /**
     * Process tickets data and count by status
     * @param {Array} tickets - Array of ticket objects
     * @returns {Object} Processed statistics
     */
    process(tickets) {
        if (!Array.isArray(tickets)) {
            Logger.error('Invalid tickets data: expected array', { tickets });
            return this._getEmptyCounts();
        }

        const counts = this._initializeCounts();
        const unknownStatuses = new Set();

        tickets.forEach(ticket => {
            this._processTicket(ticket, counts, unknownStatuses);
        });

        // Log unknown statuses
        if (unknownStatuses.size > 0) {
            Logger.warn('Unknown ticket statuses found:', Array.from(unknownStatuses));
        }

        return {
            counts,
            total: tickets.length,
            unknownStatuses: Array.from(unknownStatuses)
        };
    }

    /**
     * Process single ticket
     * @private
     */
    _processTicket(ticket, counts, unknownStatuses) {
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
    }

    /**
     * Initialize counts object
     * @private
     */
    _initializeCounts() {
        return this.validStatuses.reduce((acc, status) => {
            acc[status] = 0;
            return acc;
        }, {});
    }

    /**
     * Get empty counts object
     * @private
     */
    _getEmptyCounts() {
        return {
            counts: this._initializeCounts(),
            total: 0,
            unknownStatuses: []
        };
    }

    /**
     * Validate response structure
     * @param {Object} response - API response
     * @returns {Object} Validation result
     */
    validateResponse(response) {
        if (!response || typeof response !== 'object') {
            return {
                valid: false,
                error: 'Invalid response format'
            };
        }

        if (!response.success) {
            return {
                valid: false,
                error: response.message || 'Failed to load tickets'
            };
        }

        if (!Array.isArray(response.data)) {
            return {
                valid: false,
                error: 'Invalid data format: expected array'
            };
        }

        return { valid: true };
    }
}

export default StatisticsDataProcessor;
