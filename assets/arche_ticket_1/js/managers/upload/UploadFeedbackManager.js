import Toast                        from '../../components/Toast.js';
import { addClass, removeClass }    from '../../utils/dom.js';

class UploadFeedbackManager {
    showFeedback({ successCount, errors }, wrapper) {
        if (successCount > 0) {
            this._showSuccess(successCount, wrapper);
        }

        if (errors.length > 0) {
            this._showErrors(errors, wrapper);
        }
    }

    _showSuccess(count, wrapper) {
        const plural = count > 1 ? 's' : '';
        Toast.success(`Added ${count} file${plural}`);
        removeClass(wrapper, 'error');
    }

    _showErrors(errors, wrapper) {
        addClass(wrapper, 'error');

        // Show only unique errors
        const uniqueErrors = [...new Set(errors)];
        Toast.error(uniqueErrors[0]);

        // Auto-remove error state after 3s
        setTimeout(() => {
            removeClass(wrapper, 'error');
        }, 3000);
    }

    clearError(wrapper) {
        removeClass(wrapper, 'error');
    }
}

export default UploadFeedbackManager;

