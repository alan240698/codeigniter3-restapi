import { CONFIG } from '../config/constants.js';
import Logger from '../utils/logger.js';

class ApiService {
    constructor() {
        this.baseURL = CONFIG.BASE_URL;
        this.timeout = 30000; // 30 seconds default timeout
        this.retryAttempts = 2;
        this.retryDelay = 1000; // 1 second
    }

    /**
     * Make HTTP request with error handling and retry logic
     * @param {string} url - Request URL
     * @param {Object} options - Fetch options
     * @param {number} retryCount - Current retry attempt
     * @returns {Promise<Object>} Response data
     */
    async request(url, options = {}, retryCount = 0) {
        const config = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            signal: this._createTimeoutSignal(options.timeout || this.timeout),
            ...options
        };

        // Don't set Content-Type for FormData (browser sets it with boundary)
        if (options.body instanceof FormData) {
            delete config.headers['Content-Type'];
        } else if (options.body && typeof options.body === 'object') {
            // Set JSON content type for object bodies
            config.headers['Content-Type'] = 'application/json';
            config.body = JSON.stringify(options.body);
        }

        try {
            Logger.group(`🌐 API Request: ${config.method} ${url}`);
            Logger.log('Config:', { 
                method: config.method, 
                headers: config.headers,
                hasBody: !!config.body 
            });

            const response = await fetch(url, config);
            const data = await this._parseResponse(response);

            Logger.log('Response:', data);
            Logger.groupEnd();

            // Check response status
            if (!response.ok) {
                throw this._createApiError(response, data);
            }

            return data;

        } catch (error) {
            Logger.error('API Error:', error);
            Logger.groupEnd();

            // Retry logic for network errors
            if (this._shouldRetry(error, retryCount)) {
                Logger.warn(`Retrying request (${retryCount + 1}/${this.retryAttempts})...`);
                await this._delay(this.retryDelay);
                return this.request(url, options, retryCount + 1);
            }

            throw this._normalizeError(error);
        }
    }

    /**
     * Parse response text to JSON with error handling
     * @private
     */
    async _parseResponse(response) {
        const responseText = await response.text();

        // Check for empty response
        if (!responseText.trim()) {
            Logger.warn('Empty response received');
            return { success: false, message: 'Empty response from server' };
        }

        // Check for HTML error responses (PHP errors, etc.)
        if (responseText.trim().startsWith('<') || responseText.trim().startsWith('<!DOCTYPE')) {
            Logger.error('HTML Error Response:', responseText.substring(0, 500));
            throw new Error('Server returned HTML error. Check server logs.');
        }

        // Parse JSON
        try {
            return JSON.parse(responseText);
        } catch (parseError) {
            Logger.error('JSON parse error:', parseError);
            Logger.error('Raw response:', responseText.substring(0, 500));
            throw new Error('Invalid JSON response from server');
        }
    }

    /**
     * Create timeout signal for fetch
     * @private
     */
    _createTimeoutSignal(timeout) {
        const controller = new AbortController();
        setTimeout(() => controller.abort(), timeout);
        return controller.signal;
    }

    /**
     * Create standardized API error
     * @private
     */
    _createApiError(response, data) {
        const error = new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
        error.status = response.status;
        error.statusText = response.statusText;
        error.data = data;
        return error;
    }

    /**
     * Normalize different error types
     * @private
     */
    _normalizeError(error) {
        if (error.name === 'AbortError') {
            const timeoutError = new Error('Request timeout. Please try again.');
            timeoutError.isTimeout = true;
            return timeoutError;
        }

        if (error.message === 'Failed to fetch') {
            const networkError = new Error('Network error. Please check your connection.');
            networkError.isNetworkError = true;
            return networkError;
        }

        return error;
    }

    /**
     * Check if request should be retried
     * @private
     */
    _shouldRetry(error, retryCount) {
        // Don't retry if max attempts reached
        if (retryCount >= this.retryAttempts) {
            return false;
        }

        // Retry on network errors or timeouts
        if (error.isNetworkError || error.isTimeout) {
            return true;
        }

        // Retry on 5xx server errors
        if (error.status >= 500 && error.status < 600) {
            return true;
        }

        return false;
    }

    /**
     * Delay helper for retry logic
     * @private
     */
    _delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * GET request
     * @param {string} url - Request URL
     * @param {Object} params - Query parameters
     * @returns {Promise<Object>}
     */
    async get(url, params = {}) {
        // Remove empty/null/undefined params
        const cleanParams = Object.entries(params).reduce((acc, [key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                acc[key] = value;
            }
            return acc;
        }, {});

        const queryString = new URLSearchParams(cleanParams).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;
        
        return this.request(fullUrl, { method: 'GET' });
    }

    /**
     * POST request
     * @param {string} url - Request URL
     * @param {FormData|Object} body - Request body
     * @returns {Promise<Object>}
     */
    async post(url, body) {
        return this.request(url, {
            method: 'POST',
            body
        });
    }

    /**
     * PUT request
     * @param {string} url - Request URL
     * @param {FormData|Object} body - Request body
     * @returns {Promise<Object>}
     */
    async put(url, body) {
        return this.request(url, {
            method: 'PUT',
            body
        });
    }

    /**
     * DELETE request
     * @param {string} url - Request URL
     * @returns {Promise<Object>}
     */
    async delete(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    }

    // ========================================
    // Ticket-specific methods
    // ========================================

    /**
     * Create new ticket
     * @param {FormData} formData - Ticket form data
     * @returns {Promise<Object>}
     */
    async createTicket(formData) {
        if (!(formData instanceof FormData)) {
            throw new Error('createTicket expects FormData');
        }

        return this.post(CONFIG.ENDPOINTS.CREATE, formData);
    }

    /**
     * List tickets with optional filters
     * @param {Object} filters - Filter parameters
     * @returns {Promise<Object>}
     */
    async listTickets(filters = {}) {
        return this.get(CONFIG.ENDPOINTS.LIST, filters);
    }

    /**
     * Get single ticket by ID
     * @param {number|string} id - Ticket ID
     * @returns {Promise<Object>}
     */
    async getTicket(id) {
        if (!id) {
            throw new Error('Ticket ID is required');
        }

        return this.get(`${CONFIG.ENDPOINTS.VIEW}${id}`);
    }

    /**
     * Update ticket
     * @param {number|string} id - Ticket ID
     * @param {FormData|Object} data - Update data
     * @returns {Promise<Object>}
     */
    async updateTicket(id, data) {
        if (!id) {
            throw new Error('Ticket ID is required');
        }

        return this.put(`${CONFIG.ENDPOINTS.REOPEN || CONFIG.ENDPOINTS.VIEW}${id}`, data);
    }

    /**
     * Delete ticket
     * @param {number|string} id - Ticket ID
     * @returns {Promise<Object>}
     */
    async deleteTicket(id) {
        if (!id) {
            throw new Error('Ticket ID is required');
        }

        return this.delete(`${CONFIG.ENDPOINTS.DELETE || CONFIG.ENDPOINTS.VIEW}${id}`);
    }

    /**
     * Upload attachment
     * @param {File} file - File to upload
     * @returns {Promise<Object>}
     */
    async uploadAttachment(file) {
        if (!(file instanceof File)) {
            throw new Error('uploadAttachment expects File object');
        }

        const formData = new FormData();
        formData.append('file', file);

        return this.post(CONFIG.ENDPOINTS.UPLOAD || '/api/upload', formData);
    }

    /**
     * Set custom timeout for next request
     * @param {number} timeout - Timeout in milliseconds
     * @returns {ApiService} - For chaining
     */
    withTimeout(timeout) {
        this._customTimeout = timeout;
        return this;
    }

    /**
     * Disable retry for next request
     * @returns {ApiService} - For chaining
     */
    withoutRetry() {
        this._disableRetry = true;
        return this;
    }
}

export default new ApiService();