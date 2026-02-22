const TicketNotifier = {
    // Show loading overlay
    showLoading(message = 'Processing...') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },

    // Hide loading
    hideLoading() {
        Swal.close();
    },

    // Success message
    showSuccess(message, callback = null) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (callback && result.isConfirmed) {
                callback();
            }
        });
    },

    // Error message
    showError(message) {
        const isHtml = /<\/?[a-z][\s\S]*>/i.test(message);

        Swal.fire({
            icon: 'error',
            title: 'Error!',
            ...(isHtml
            ? { html: message }
            : { text: message }),
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'OK'
        });
    },

    // Confirmation dialog
    async confirm(title, message, confirmText = 'Yes, delete it!') {
        const result = await Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel'
        });
        return result.isConfirmed;
    },

    // Validation error
    showValidationError(message) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: message,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'OK'
        });
    },

    // Toast notification (non-blocking)
    showToast(message, type = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: type,
            title: message
        });
    }
};

const UITicketStatus = {
    updateStepStatus(step, status) {
        const statusContainer = step.querySelector('.step-status');

        const statusHTML = {
            completed: `
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.9); padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                    <i class="fas fa-check-circle"></i>
                    <span>Completed</span>
                </div>
            `,
            active: `
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.95); color: #fa709a; padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                    <i class="fas fa-arrow-right" style="animation: arrow-move 1s infinite;"></i>
                    <span>In Progress</span>
                </div>
            `,
            locked: `
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(251, 191, 36, 0.9); padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);">
                    <i class="fas fa-lock"></i>
                    <span>Locked</span>
                </div>
            `
        };

        statusContainer.innerHTML = statusHTML[status];
    },
};

// Enhanced fetch with loading and error handling
async function fetchWithLoading(url, options = {}, loadingMessage = 'Loading...') {
    try {
        TicketNotifier.showLoading(loadingMessage);
        
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });

        const data = await response.json();
        TicketNotifier.hideLoading();

        return data;
    } catch (error) {
        TicketNotifier.hideLoading();
        TicketNotifier.showError('Network error. Please check your connection.');
        throw error;
    }
}

// Enhanced save with loading and success/error handling
async function saveWithFeedback(url, formData, successMessage, loadingMessage = 'Saving...') {
    try {
        const data = await fetchWithLoading(url, {
            method: 'POST',
            body: JSON.stringify(formData)
        }, loadingMessage);

        if (data.success) {
            TicketNotifier.showSuccess(successMessage);
            return true;
        } else {
            TicketNotifier.showError(data.message || 'An error occurred');
            return false;
        }
    } catch (error) {
        return false;
    }
}

// Enhanced delete with confirmation
async function deleteWithConfirmation(url, itemName, successCallback) {
    const confirmed = await TicketNotifier.confirm(
        'Are you sure?',
        `Do you want to delete this ${itemName}? This action cannot be undone.`,
        'Yes, delete it!'
    );

    if (!confirmed) return false;

    try {
        const data = await fetchWithLoading(url, {
            method: 'POST'
        }, 'Deleting...');

        if (data.success) {
            TicketNotifier.showSuccess(`${itemName} deleted successfully`, successCallback);
            return true;
        } else {
            TicketNotifier.showError(data.message || 'Failed to delete');
            return false;
        }
    } catch (error) {
        return false;
    }
}
