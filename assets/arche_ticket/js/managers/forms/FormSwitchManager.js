import EventBus from '../../core/EventBus.js';
import Logger from '../../utils/logger.js';

class FormSwitchManager {
    constructor(stateManager, resetManager) {
        this.stateManager = stateManager;
        this.resetManager = resetManager;
        this._bindEvents();
    }

    /**
     * Bind form switching events
     */
    _bindEvents() {
        EventBus.on('form:switch', ({ fromFormId, toFormId }) => {
            this._handleFormSwitch(fromFormId, toFormId);
        });

        EventBus.on('card:expanded', ({ category }) => {
            this._handleCardExpanded(category);
        });
    }

    /**
     * Handle form switch event
     */
    _handleFormSwitch(fromFormId, toFormId) {
        Logger.info('Form switching', { fromFormId, toFormId });
        
        if (fromFormId && fromFormId !== toFormId) {
            const previousForm = document.querySelector(`form[data-category="${fromFormId}"]`);
            if (previousForm) {
                this.resetManager.resetFormOnSwitch(previousForm, fromFormId);
            }
        }
        
        this.stateManager.setActiveForm(toFormId);
    }

    /**
     * Handle card expanded event
     */
    _handleCardExpanded(category) {
        const currentActive = this.stateManager.getCurrentActiveForm();
        
        if (currentActive && currentActive !== category) {
            const previousForm = document.querySelector(`form[data-category="${currentActive}"]`);
            if (previousForm) {
                this.resetManager.resetFormOnSwitch(previousForm, currentActive);
            }
        }
        
        this.stateManager.setActiveForm(category);
    }
}

export default FormSwitchManager;