import Logger from '../../utils/logger.js';

class TicketPaginationManager {
    constructor(itemsPerPage = 2) {
        this.currentPage = 1;
        this.itemsPerPage = itemsPerPage;
    }

    /**
     * Set items per page
     */
    setItemsPerPage(count) {
        this.itemsPerPage = count;
        this.currentPage = 1;
    }

    /**
     * Go to page
     */
    goToPage(page, totalItems) {
        const totalPages = this.getTotalPages(totalItems);

        if (page < 1 || page > totalPages) {
            Logger.warn('Invalid page number:', page);
            return false;
        }

        this.currentPage = page;
        return true;
    }

    /**
     * Get total pages
     */
    getTotalPages(totalItems) {
        return Math.ceil(totalItems / this.itemsPerPage);
    }

    /**
     * Get paginated items
     */
    getPaginatedItems(items) {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return items.slice(start, end);
    }

    /**
     * Get current page
     */
    getCurrentPage() {
        return this.currentPage;
    }

    /**
     * Reset to first page
     */
    reset() {
        this.currentPage = 1;
    }
}

export default TicketPaginationManager;