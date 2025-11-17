import Logger from '../../utils/logger.js';
import ApiResponseParser from './ApiResponseParser.js';
import ApiErrorHandler from './ApiErrorHandler.js';
import ApiRetryManager from './ApiRetryManager.js';

class ApiRequestHandler {
    constructor() {
        this.timeout = 30000; // 30 seconds
        this.responseParser = new ApiResponseParser();
        this.errorHandler = new ApiErrorHandler();
        this.retryManager = new ApiRetryManager();

        // Temporary config for fluent API
        this._customTimeout = null;
        this._disableRetry = false;
    }

    async request(url, options = {}, retryCount = 0) {
        const config = this._buildConfig(options);

        try {
            Logger.group(`API Request: ${config.method} ${url}`);
            Logger.log('Config:', { 
                method: config.method, 
                headers: config.headers,
                hasBody: !!config.body 
            });

            const response = await fetch(url, config);
            const data = await this.responseParser.parse(response);

            Logger.log('Response:', data);
            Logger.groupEnd();

            if (!response.ok) {
                throw this.errorHandler.createApiError(response, data);
            }

            return data;

        } catch (error) {
            Logger.error('API Error:', error);
            Logger.groupEnd();

            // Check if should retry
            const shouldRetry = !this._disableRetry && this.retryManager.shouldRetry(error, retryCount);

            if (shouldRetry) {
                Logger.warn(`Retrying request (${retryCount + 1}/${this.retryManager.maxAttempts})...`);
                await this.retryManager.delay();
                return this.request(url, options, retryCount + 1);
            }

            throw this.errorHandler.normalize(error);
        } finally {
            // Reset temporary config
            this._customTimeout = null;
            this._disableRetry = false;
        }
    }

    _buildConfig(options) {
        const config = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            signal: this._createTimeoutSignal(
                this._customTimeout || options.timeout || this.timeout
            ),
            ...options
        };

        // Handle body
        if (options.body instanceof FormData) {
            delete config.headers['Content-Type'];
        } else if (options.body && typeof options.body === 'object') {
            config.headers['Content-Type'] = 'application/json';
            config.body = JSON.stringify(options.body);
        }

        return config;
    }

    _createTimeoutSignal(timeout) {
        const controller = new AbortController();
        setTimeout(() => controller.abort(), timeout);
        return controller.signal;
    }

    // HTTP Methods
    async get(url, params = {}) {
        const cleanParams = this._cleanParams(params);
        const queryString = new URLSearchParams(cleanParams).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;

        return this.request(fullUrl, { method: 'GET' });
    }

    async post(url, body) {
        return this.request(url, { method: 'POST', body });
    }

    async put(url, body) {
        return this.request(url, { method: 'PUT', body });
    }

    async delete(url) {
        return this.request(url, { method: 'DELETE' });
    }

    _cleanParams(params) {
        return Object.entries(params).reduce((acc, [key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                acc[key] = value;
            }
            return acc;
        }, {});
    }

    // Fluent API setters
    setTimeout(timeout) {
        this._customTimeout = timeout;
    }

    disableRetry() {
        this._disableRetry = true;
    }
}

export default ApiRequestHandler;