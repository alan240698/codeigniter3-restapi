import ApiService           from '../../services/api/index.js';
import Toast                from '../../components/Toast.js';
import EventBus             from '../../core/EventBus.js';
import Logger               from '../../utils/logger.js';

class FormSubmitManager {
    constructor(stateManager, validationManager, dataCollector, resetManager) {
        this.stateManager = stateManager;
        this.validationManager = validationManager;
        this.dataCollector = dataCollector;
        this.resetManager = resetManager;
    }

async submit(form) {
    const category = form.dataset.category;
    
    // Prevent double submission
    if (this.stateManager.isFormSubmitting(category)) {
        Logger.warn('Form already submitting', { category });
        return;
    }

    // ✅ SET NGAY LẬP TỨC để block các click tiếp theo
    this.stateManager.setSubmitting(category, true);

    const btn = form.querySelector('.submit-btn');
    const btnText = btn?.querySelector('span');
    
    if (!btn || !btnText) {
        Logger.error('Submit button or text not found');
        Toast.error('Form configuration error');
        this.stateManager.setSubmitting(category, false); // ✅ Reset nếu có lỗi
        return;
    }

    const originalText = btnText.textContent;

    // Clear previous errors
    this.validationManager.clearFormErrors(form);

    // Validate form
    const validation = this.validationManager.validateForm(form);
    if (!validation.valid) {
        this.stateManager.setSubmitting(category, false); // ✅ Reset nếu validation fail
        return;
    }

    // Update button state
    this._updateButtonState(btn, btnText, true);

    try {
        const formData = this.dataCollector.collectFormData(form, category);
        const result = await ApiService.createTicket(formData);

        if (result.success) {
            this._handleSuccess(form, category, result);
        } else {
            this.validationManager.handleServerErrors(form, result);
        }
    } catch (error) {
        Logger.error('Form submit error:', error);
        const errorMsg = error.message || 'An error occurred. Please try again!';
        Toast.error(errorMsg);
    } finally {
        // Reset button state
        this.stateManager.setSubmitting(category, false);
        this._updateButtonState(btn, btnText, false, originalText);
    }
}

    /**
     * Handle successful form submission
     */
    _handleSuccess(form, category, result) {
        const ticketId = result.ticket_id || 'N/A';
        Toast.success(`Ticket #${ticketId} has been created and assigned.<br/>You'll receive an email soon.`);

        this.resetManager.resetForm(form, category);

        EventBus.emit('ticket:created', result);

        // Collapse card after delay
        setTimeout(() => {
            EventBus.emit('card:collapse');
        }, 1500);
    }

    /**
     * Update button state
     */
    _updateButtonState(btn, btnText, isLoading, originalText = 'Send Request') {
        btn.disabled = isLoading;
        
        if (isLoading) {
            btnText.textContent = 'Sending...';
            btn.innerHTML = `<div class="spinner"></div><span>${btnText.textContent}</span>`;
        } else {
            btn.innerHTML = `<i class="fas fa-paper-plane"></i><span>${originalText}</span>`;
        }
    }
}

export default FormSubmitManager;