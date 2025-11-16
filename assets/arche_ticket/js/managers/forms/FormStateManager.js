import Logger       from '../../utils/logger.js';
import EventBus     from '../../core/EventBus.js';

class FormStateManager {
    constructor() {
        this.isSubmitting = new Map();
        this.currentActiveForm = null;
        this._bindEvents();
    }

    /**
     * Check if form is submitting
     */
    isFormSubmitting(category) {
        return this.isSubmitting.get(category) || false;
    }

    /**
     * Set form submitting state
     */
    setSubmitting(category, state) {
        this.isSubmitting.set(category, state);
        Logger.info('Form submission state changed', { category, submitting: state });
    }

    /**
     * Get current active form
     */
    getCurrentActiveForm() {
        return this.currentActiveForm;
    }

    /**
     * Set current active form
     */
    setActiveForm(category) {
        this.currentActiveForm = category;
    }

    /**
     * Bind state events
     */
    _bindEvents() {
        EventBus.on('form:switch', ({ fromFormId, toFormId }) => {
            Logger.info('Form switching', { fromFormId, toFormId });
            this.currentActiveForm = toFormId;
        });

        EventBus.on('card:expanded', ({ category }) => {
            this.currentActiveForm = category;
        });
    }
}

export default FormStateManager;