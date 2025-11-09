import { CONFIG } from '../../config/constants.js';

class TicketApiService {
    constructor(requestHandler) {
        this.request = requestHandler;
    }

    /**
     * Create new ticket
     */
    async create(formData) {
        if (!(formData instanceof FormData)) {
            throw new Error('createTicket expects FormData');
        }

        return this.request.post(CONFIG.ENDPOINTS.CREATE, formData);
    }

    /**
     * List tickets with filters
     */
    async list(filters = {}) {
        return this.request.get(CONFIG.ENDPOINTS.LIST, filters);
    }

    /**
     * Get single ticket by ID
     */
    async get(id) {
        this._validateId(id);
        return this.request.get(`${CONFIG.ENDPOINTS.VIEW}${id}`);
    }

    /**
     * Update ticket
     */
    async update(id, data) {
        this._validateId(id);
        const endpoint = CONFIG.ENDPOINTS.REOPEN || CONFIG.ENDPOINTS.VIEW;
        return this.request.put(`${endpoint}${id}`, data);
    }

    /**
     * Delete ticket
     */
    async delete(id) {
        this._validateId(id);
        const endpoint = CONFIG.ENDPOINTS.DELETE || CONFIG.ENDPOINTS.VIEW;
        return this.request.delete(`${endpoint}${id}`);
    }

    /**
     * Upload attachment
     */
    async uploadAttachment(file) {
        if (!(file instanceof File)) {
            throw new Error('uploadAttachment expects File object');
        }

        const formData = new FormData();
        formData.append('file', file);

        const endpoint = CONFIG.ENDPOINTS.UPLOAD || '/api/upload';
        return this.request.post(endpoint, formData);
    }

    _validateId(id) {
        if (!id) {
            throw new Error('Ticket ID is required');
        }
    }
}

export default TicketApiService;