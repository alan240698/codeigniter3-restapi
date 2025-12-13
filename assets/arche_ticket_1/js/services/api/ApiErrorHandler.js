class ApiErrorHandler {
    createApiError(response, data) {
        const error = new Error(
            data.message || `HTTP ${response.status}: ${response.statusText}`
        );
        error.status = response.status;
        error.statusText = response.statusText;
        error.data = data;
        return error;
    }

    normalize(error) {
        if (error.name === 'AbortError') {
            return this._createTimeoutError();
        }

        if (error.message === 'Failed to fetch') {
            return this._createNetworkError();
        }

        return error;
    }

    _createTimeoutError() {
        const error = new Error('Request timeout. Please try again.');
        error.isTimeout = true;
        return error;
    }

    _createNetworkError() {
        const error = new Error('Network error. Please check your connection.');
        error.isNetworkError = true;
        return error;
    }
}

export default ApiErrorHandler;