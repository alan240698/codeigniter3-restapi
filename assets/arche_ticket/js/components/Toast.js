import { CONFIG, TOAST_ICONS } from '../config/constants.js';
import { createElement } from '../utils/dom.js';

class Toast {
    constructor() {
        this.container = this._getOrCreateContainer();
    }

    show(message, type = 'success') {
        const icon = TOAST_ICONS[type] || TOAST_ICONS.info;
        const formattedMessage = message.replace(/\n/g, '<br>');

        const html = `
            <div class="toast toast-${type}">
                <i class="fas ${icon}"></i>
                <div class="toast-message">${formattedMessage}</div>
                <button class="toast-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        const toast = createElement(html);
        
        // Close button handler
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this._removeToast(toast);
        });

        this.container.appendChild(toast);
        
        // Show animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto remove
        setTimeout(() => {
            this._removeToast(toast);
        }, CONFIG.UI.TOAST_DURATION);
    }

    success(message) {
        this.show(message, 'success');
    }

    error(message) {
        this.show(message, 'error');
    }

    warning(message) {
        this.show(message, 'warning');
    }

    info(message) {
        this.show(message, 'info');
    }

    _removeToast(toast) {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }

    _getOrCreateContainer() {
        let container = document.getElementById('toastContainer');
        
        if (!container) {
            container = createElement('<div id="toastContainer" class="toast-container"></div>');
            document.body.appendChild(container);
        }

        return container;
    }
}

export default new Toast();