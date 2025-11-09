class StatisticsConfig {
    /**
     * Get valid statuses
     * @returns {Array<string>}
     */
    static getValidStatuses() {
        return ['new', 'pending', 'processing', 'solved', 'closed'];
    }

    /**
     * Get animation config
     * @returns {Object}
     */
    static getAnimationConfig() {
        return {
            duration: 500,
            steps: 20,
            scaleDuration: 200,
            scale: 1.1
        };
    }

    /**
     * Check if status is valid
     * @param {string} status - Status to check
     * @returns {boolean}
     */
    static isValidStatus(status) {
        return this.getValidStatuses().includes(status?.toLowerCase());
    }
}

export default StatisticsConfig;