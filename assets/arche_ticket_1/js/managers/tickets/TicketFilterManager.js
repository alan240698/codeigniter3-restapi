import Logger from '../../utils/logger.js';

class TicketFilterManager {
    constructor() {
        this.allTickets = [];
        this.filteredTickets = [];
        this.currentFilters = null;
    }

    /**
     * Set all tickets
     */
    setAllTickets(tickets) {
        this.allTickets = Array.isArray(tickets) ? tickets : [];
        this.applyFilters();
    }

    /**
     * Set filters
     */
    setFilters(filters) {
        this.currentFilters = filters;
        this.applyFilters();
    }

    /**
     * Get filtered tickets
     */
    getFilteredTickets() {
        return this.filteredTickets;
    }

    /**
     * Get current filters
     */
    getCurrentFilters() {
        return this.currentFilters;
    }

    /**
     * Clear filters
     */
    clearFilters() {
        this.currentFilters = null;
        this.applyFilters();
    }

    /**
     * Apply filters to tickets
     */
    applyFilters() {
        if (!this.currentFilters || !this.currentFilters.status) {
            this.filteredTickets = [...this.allTickets];
        } else {
            const status = this.currentFilters.status.toLowerCase();
            this.filteredTickets = this.allTickets.filter(ticket =>
                (ticket.status || '').toLowerCase() === status
            );
        }

        Logger.info('Filtered tickets:', {
            total: this.allTickets.length,
            filtered: this.filteredTickets.length,
            filter: this.currentFilters
        });
    }
}

export default TicketFilterManager;