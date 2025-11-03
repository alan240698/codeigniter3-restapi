import StatisticsManager from './managers/StatisticsManager.js';
import FormManager from './managers/FormManager.js';
import CardExpansion from './components/CardExpansion.js';
import Timestamp from './components/Timestamp.js';
import EventBus from './core/EventBus.js';
import Logger from './utils/logger.js';
import './styles/validation-inject.js';
import FileUploadHandler from './managers/FileUploadHandler.js';
import TicketModalManager from './managers/TicketModalManager.js';

class TicketApp {
    constructor() {
        this.initialized = false;
    }

    async init() {
        if (this.initialized) return;

        try {
            Logger.group('Initializing Ticket System');

            // Initialize components
            CardExpansion.init();
            FormManager.init();
            Timestamp.start();
            FileUploadHandler.init();

            // Load data
            await StatisticsManager.load();

            // Setup event listeners
            this._setupEventListeners();

            this.initialized = true;
            Logger.log('Ticket System Initialized');
            Logger.groupEnd();

            EventBus.emit('app:ready');
        } catch (error) {
            Logger.error('Initialization failed:', error);
            Logger.groupEnd();
        }
    }

    _setupEventListeners() {
        // Listen to events
        EventBus.on('ticket:created', () => {
            StatisticsManager.load();
        });

        EventBus.on('card:collapse', () => {
            CardExpansion.collapse();
        });

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                EventBus.emit('card:collapse');
            }
        });
    }

    destroy() {
        Timestamp.stop();
        this.initialized = false;
        EventBus.emit('app:destroyed');
    }
}

// Create and export app instance
const app = new TicketApp();

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => app.init());
} else {
    app.init();
}

// Export for global access
window.TicketApp = app;
window.TicketModal = TicketModalManager;

export default app;