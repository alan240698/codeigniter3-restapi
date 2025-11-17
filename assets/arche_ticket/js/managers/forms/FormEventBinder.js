import { $$ } from '../../utils/dom.js';

class FormEventBinder {
    constructor(submitManager, validationManager) {
        this.submitManager = submitManager;
        this.validationManager = validationManager;
    }

    /**
     * Bind all form events
     */
    bindEvents() {
        this._bindSubmitEvents();
    }

    /**
     * Bind form submit events
     */
    _bindSubmitEvents() {
        $$('.ticket-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitManager.submit(form);
            });

            this.validationManager.enableRealtimeValidation(form);
        });
    }
}

export default FormEventBinder;