const injectValidationStyles = () => {
    if (document.getElementById('validation-styles')) return;

    const style = document.createElement('style');
    style.id = 'validation-styles';
    style.textContent = `
        /* Field Error Styles */
        .form-control.error,
        .form-select.error {
            border-color: #ef4444 !important;
            background-color: #fef2f2;
            animation: shake 0.3s ease;
        }

        .field-error-message {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #fef2f2;
            border-left: 3px solid #ef4444;
            border-radius: 4px;
            color: #dc2626;
            font-size: 0.875rem;
            animation: slideDown 0.3s ease;
        }

        .field-error-message i {
            color: #ef4444;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
        }

.toast-container {
    position: fixed;
    top: 25px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 12px;
    pointer-events: none;
    max-width: 650px;
    width: 100%;
    padding: 0 20px;
}

.toast {
    pointer-events: all;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px 24px;
    width: 100%;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    transform: translateX(-100%);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.toast.show {
    transform: translateX(0);
    opacity: 1;
}

/* Icon Circle */
.toast i:first-child {
    width: 44px;
    height: 44px;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
    border-radius: 50%;
    font-style: normal;
}

/* Message */
.toast-message {
    flex: 1;
    font-size: 15px;
    color: #1f2937;
    line-height: 1.6;
    margin: 0;
    padding-top: 2px;
}

/* Close Button */
.toast-close {
    width: 24px;
    height: 24px;
    min-width: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0;
    transition: all 0.2s ease;
    border-radius: 4px;
    font-size: 1rem;
    margin-top: 2px;
}

.toast-close i {
    width: auto;
    height: auto;
    min-width: auto;
    background: none !important;
    border-radius: 0;
    font-size: inherit;
    color: inherit;
}

.toast-close:hover {
    background: #f3f4f6;
    color: #4b5563;
}

.toast-close:active {
    transform: scale(0.9);
}

/* Info Style */
.toast-info {
    border-left: 4px solid #3b82f6;
}

.toast-info i:first-child {
    background: #3b82f6;
    color: white;
}

/* Warning Style */
.toast-warning {
    border-left: 4px solid #f59e0b;
}

.toast-warning i:first-child {
    background: #f59e0b;
    color: white;
}

/* Success Style */
.toast-success {
    border-left: 4px solid #10b981;
}

.toast-success i:first-child {
    background: #10b981;
    color: white;
}

/* Error Style */
.toast-error {
    border-left: 4px solid #ef4444;
}

.toast-error i:first-child {
    background: #ef4444;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .toast-container {
        top: 16px;
        left: 16px;
        right: 16px;
        transform: none;
        max-width: none;
        padding: 0;
    }

    .toast {
        padding: 16px 20px;
    }

    .toast i:first-child {
        width: 40px;
        height: 40px;
        min-width: 40px;
        font-size: 1.125rem;
    }

    .toast-message {
        font-size: 14px;
    }

    .toast-close {
        width: 20px;
        height: 20px;
        min-width: 20px;
        font-size: 1.125rem;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .toast {
        background: #1f2937;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }

    .toast-message {
        color: #f9fafb;
    }

    .toast-close {
        color: #d1d5db;
    }

    .toast-close:hover {
        background: #374151;
        color: #f9fafb;
    }
}

        /* File Upload Error */
        .file-upload-wrapper.error {
            border-color: #ef4444 !important;
            background: #fef2f2 !important;
            animation: shake 0.5s;
        }

        /* Spinner */
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Loading state */
        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Focus styles for accessibility */
        .form-control:focus,
        .form-select:focus {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }

        .form-control.error:focus,
        .form-select.error:focus {
            outline-color: #ef4444;
        }
    `;

    document.head.appendChild(style);
};

// Auto-inject when imported
injectValidationStyles();

export default injectValidationStyles;