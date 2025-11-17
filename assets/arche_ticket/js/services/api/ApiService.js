import { CONFIG }           from '../../config/constants.js';
import ApiRequestHandler    from './ApiRequestHandler.js';
import TicketApiService     from './TicketApiService.js';

class ApiService {
    constructor() {
        this.baseURL = CONFIG.BASE_URL;
        this.requestHandler = new ApiRequestHandler();
        this.tickets = new TicketApiService(this.requestHandler);
    }

    /**
     * GET request
     */
    async get(url, params = {}) {
        return this.requestHandler.get(url, params);
    }

    /**
     * POST request
     */
    async post(url, body) {
        return this.requestHandler.post(url, body);
    }

    /**
     * PUT request
     */
    async put(url, body) {
        return this.requestHandler.put(url, body);
    }

    /**
     * DELETE request
     */
    async delete(url) {
        return this.requestHandler.delete(url);
    }

    /**
     * Fluent API: Set custom timeout
     */
    withTimeout(timeout) {
        this.requestHandler.setTimeout(timeout);
        return this;
    }

    /**
     * Fluent API: Disable retry
     */
    withoutRetry() {
        this.requestHandler.disableRetry();
        return this;
    }

    // Backward compatibility
    createTicket(formData) {
        return this.tickets.create(formData);
    }

    listTickets(filters) {
        return this.tickets.list(filters);
    }

    getTicket(id) {
        return this.tickets.get(id);
    }

    updateTicket(id, data) {
        return this.tickets.update(id, data);
    }

    deleteTicket(id) {
        return this.tickets.delete(id);
    }

    uploadAttachment(file) {
        return this.tickets.uploadAttachment(file);
    }

    downloadAttachment(docId, ticketId) {
        return this.tickets.downloadTicketDocument(docId, ticketId);
    } 
}

export default new ApiService();