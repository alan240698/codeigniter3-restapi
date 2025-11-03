import { CONFIG } from '../config/constants.js';
import Logger from '../utils/logger.js';

class ApiService {
    constructor() {
        this.baseURL = CONFIG.BASE_URL;
    }

    async request(url, options = {}) {
        const config = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        };

        // Don't set Content-Type for FormData
        if (options.body instanceof FormData) {
            delete config.headers['Content-Type'];
        }

        try {
            Logger.group(`🌐 API Request: ${config.method} ${url}`);
            Logger.log('Config:', config);

            const response = await fetch(url, config);
            const responseText = await response.text();

            // Check for HTML error responses
            if (responseText.trim().startsWith('<')) {
                Logger.error('HTML Error Response:', responseText.substring(0, 500));
                throw new Error('Server returned HTML error. Check PHP error logs.');
            }

            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                Logger.error('JSON parse error:', parseError);
                Logger.error('Raw response:', responseText.substring(0, 500));
                throw new Error('Server returned invalid JSON format');
            }

            Logger.log('✅ Response:', data);
            Logger.groupEnd();

            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            Logger.error('❌ API Error:', error);
            Logger.groupEnd();
            throw error;
        }
    }

    async get(url, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;
        return this.request(fullUrl, { method: 'GET' });
    }

    async post(url, body) {
        return this.request(url, {
            method: 'POST',
            body
        });
    }

    // Ticket-specific methods
    async createTicket(formData) {
        return this.post(CONFIG.ENDPOINTS.CREATE, formData);
    }

    async listTickets(filters = {}) {
        return this.get(CONFIG.ENDPOINTS.LIST, filters);
    }

    async getTicket(id) {
        return this.get(`${CONFIG.ENDPOINTS.VIEW}${id}`);
    }
}

export default new ApiService();
