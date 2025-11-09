import FormStateManager from './FormStateManager.js';
import FormValidationManager from './FormValidationManager.js';
import FormDataCollector from './FormDataCollector.js';
import FormResetManager from './FormResetManager.js';
import FormSubmitManager from './FormSubmitManager.js';
import FormSwitchManager from './FormSwitchManager.js';
import FormEventBinder from './FormEventBinder.js';

class FormManager {
    constructor() {
        // Initialize sub-managers
        this.stateManager = new FormStateManager();
        this.validationManager = new FormValidationManager();
        this.dataCollector = new FormDataCollector();
        this.resetManager = new FormResetManager(this.validationManager);
        this.submitManager = new FormSubmitManager(
            this.stateManager,
            this.validationManager,
            this.dataCollector,
            this.resetManager
        );
        this.switchManager = new FormSwitchManager(this.stateManager, this.resetManager);
        this.eventBinder = new FormEventBinder(this.submitManager, this.validationManager);
    }

    /**
     * Initialize FormManager
     */
    init() {
        this.eventBinder.bindEvents();
    }

    /**
     * Submit form (public API for compatibility)
     */
    async submit(form) {
        await this.submitManager.submit(form);
    }

    /**
     * Destroy FormManager (cleanup)
     */
    destroy() {
        // Cleanup would require storing form references
        // Consider implementing if needed for SPA
    }
}

export default new FormManager();
