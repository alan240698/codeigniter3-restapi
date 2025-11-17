class ApiRetryManager {
    constructor() {
        this.maxAttempts = 2;
        this.delayMs = 1000; // 1 second
    }

    shouldRetry(error, retryCount) {
        if (retryCount >= this.maxAttempts) {
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

    delay() {
        return new Promise(resolve => setTimeout(resolve, this.delayMs));
    }

    setMaxAttempts(attempts) {
        this.maxAttempts = attempts;
    }

    setDelay(ms) {
        this.delayMs = ms;
    }
}

export default ApiRetryManager;
