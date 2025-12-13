/**
 * API Handler for IT Ticket System
 */

const TicketAPI = {
    /**
     * Get ticket list
     */
    getList: async function () {
        try {
            const response = await $.ajax({
                url: CONFIG.API.LIST,
                method: 'GET',
                dataType: 'json'
            });

            return response;
        } catch (error) {
            console.error('Error fetching ticket list:', error);
            throw error;
        }
    },

    /**
     * Create new ticket
     */
    create: async function (formData) {
        try {
            const response = await $.ajax({
                url: CONFIG.API.CREATE,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            });

            return response;
        } catch (error) {
            console.error('Error creating ticket:', error);
            throw error;
        }
    },

    /**
     * Get ticket by ID
     */
    getById: async function (ticketId) {
        try {
            const response = await $.ajax({
                url: CONFIG.API.VIEW + ticketId,
                method: 'GET',
                dataType: 'json'
            });

            return response;
        } catch (error) {
            console.error('Error fetching ticket details:', error);
            throw error;
        }
    },

    /**
     * Reopen ticket
     */
    reopen: async function (ticketId) {
        try {
            const response = await $.ajax({
                url: CONFIG.API.REOPEN + ticketId,
                method: 'POST',
                dataType: 'json'
            });

            return response;
        } catch (error) {
            console.error('Error reopening ticket:', error);
            throw error;
        }
    },

    /**
     * Download document
     */
    downloadDocument: function (documentId) {
        window.open(CONFIG.API.DOWNLOAD + documentId, '_blank');
    }
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TicketAPI;
}