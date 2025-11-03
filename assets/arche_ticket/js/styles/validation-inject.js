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
        
        /* Toast Container */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
            pointer-events: none;
        }
        
        .toast {
            pointer-events: all;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast-success {
            border-left: 4px solid #10b981;
        }
        
        .toast-error {
            border-left: 4px solid #ef4444;
        }
        
        .toast-warning {
            border-left: 4px solid #f59e0b;
        }
        
        .toast-info {
            border-left: 4px solid #3b82f6;
        }
        
        .toast i:first-child {
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .toast-success i:first-child {
            color: #10b981;
        }
        
        .toast-error i:first-child {
            color: #ef4444;
        }
        
        .toast-warning i:first-child {
            color: #f59e0b;
        }
        
        .toast-info i:first-child {
            color: #3b82f6;
        }
        
        .toast-message {
            flex: 1;
            color: #1f2937;
            line-height: 1.5;
            font-size: 0.95rem;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border-radius: 4px;
            flex-shrink: 0;
        }
        
        .toast-close:hover {
            color: #1f2937;
            background: #f3f4f6;
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