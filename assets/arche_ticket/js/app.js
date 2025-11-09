import StatisticsManager from './managers/statistics/index.js';
import FormManager from './managers/forms/index.js';
import CardExpansion from './components/CardExpansion.js';
import Timestamp from './components/Timestamp.js';
import EventBus from './core/EventBus.js';
import Logger from './utils/logger.js';
import FileUploadManager from './managers/upload/index.js';
import TicketModalManager from './managers/tickets/index.js';
import StorageService from './services/storage/index.js';
import './styles/validation-inject.js';

class TicketApp {
    constructor() {
        this.initialized = false;
        this.isInitializing = false;
        this.components = new Map();
        this.eventListeners = [];
        this.version = '1.0.0';
        
        this.metrics = {
            startTime: null,
            initTime: null,
            readyTime: null
        };
    }

    /**
     * Initialize the application
     * @returns {Promise<boolean>} Success status
     */
    async init() {
        // Prevent double initialization
        if (this.initialized || this.isInitializing) {
            Logger.warn('App already initialized or initializing');
            return false;
        }

        this.isInitializing = true;
        this.metrics.startTime = performance.now();

        try {
            Logger.group(`🚀 Initializing Ticket System v${this.version}`);
            Logger.log('Environment:', {
                userAgent: navigator.userAgent,
                language: navigator.language,
                viewport: `${window.innerWidth}x${window.innerHeight}`
            });

            // Pre-initialization checks
            await this._preInit();

            // Initialize core components
            await this._initComponents();

            // Load initial data
            await this._loadInitialData();

            // Setup event listeners
            this._setupEventListeners();

            // Post-initialization tasks
            this._postInit();

            // Mark as initialized
            this.initialized = true;
            this.isInitializing = false;

            this.metrics.initTime = performance.now() - this.metrics.startTime;
            Logger.log(`Initialization completed in ${this.metrics.initTime.toFixed(2)}ms`);
            Logger.groupEnd();

            // Emit ready event
            EventBus.emit('app:ready', {
                version: this.version,
                initTime: this.metrics.initTime
            });

            return true;

        } catch (error) {
            this.isInitializing = false;
            Logger.error('Initialization failed:', error);
            Logger.groupEnd();

            // Emit error event
            EventBus.emit('app:error', { error });

            // Show user-friendly error
            this._showInitError(error);

            return false;
        }
    }

    /**
     * Pre-initialization checks
     * @private
     */
    async _preInit() {
        Logger.log('Running pre-initialization checks...');

        // Check browser compatibility
        if (!this._checkBrowserSupport()) {
            throw new Error('Browser not supported');
        }

        // Check required DOM elements
        if (!this._checkRequiredElements()) {
            Logger.warn('Some required elements not found, continuing anyway...');
        }

        // Clear any stale storage
        this._clearStaleStorage();
    }

    /**
     * Initialize all components
     * @private
     */
    async _initComponents() {
        Logger.log('Initializing components...');

        const componentsToInit = [
            { name: 'CardExpansion', instance: CardExpansion, critical: true },
            { name: 'FormManager', instance: FormManager, critical: true },
            { name: 'FileUploadManager', instance: FileUploadManager, critical: true },
            { name: 'Timestamp', instance: Timestamp, critical: false },
            { name: 'TicketModalManager', instance: TicketModalManager, critical: false }
        ];

        for (const component of componentsToInit) {
            try {
                if (component.instance && typeof component.instance.init === 'function') {
                    await component.instance.init();
                    this.components.set(component.name, component.instance);
                    Logger.log(`✓ ${component.name} initialized`);
                } else if (component.instance && typeof component.instance.start === 'function') {
                    component.instance.start();
                    this.components.set(component.name, component.instance);
                    Logger.log(`✓ ${component.name} started`);
                }
            } catch (error) {
                Logger.error(`Failed to initialize ${component.name}:`, error);
                
                if (component.critical) {
                    throw new Error(`Critical component ${component.name} failed to initialize`);
                }
            }
        }
    }

    /**
     * Load initial data
     * @private
     */
    async _loadInitialData() {
        Logger.log('Loading initial data...');

        try {
            // Load statistics with timeout
            const timeout = new Promise((_, reject) => 
                setTimeout(() => reject(new Error('Statistics load timeout')), 10000)
            );

            await Promise.race([
                StatisticsManager.load(),
                timeout
            ]);

            Logger.log('✓ Statistics loaded');
        } catch (error) {
            Logger.error('Failed to load statistics:', error);
            // Don't throw, continue without statistics
        }
    }

    /**
     * Setup global event listeners
     * @private
     */
    _setupEventListeners() {
        Logger.log('Setting up event listeners...');

        // EventBus listeners (use EventBus.on, not addEventListener)
        EventBus.on('ticket:created', () => {
            Logger.info('Ticket created, reloading statistics...');
            StatisticsManager.load();
        });

        EventBus.on('card:collapse', () => {
            CardExpansion.collapse();
        });

        // DOM event listeners
        this._addEventListener(document, 'keydown', (e) => {
            if (e.key === 'Escape') {
                EventBus.emit('card:collapse');
            }
        });

        // Handle visibility change (tab switching)
        this._addEventListener(document, 'visibilitychange', () => {
            if (!document.hidden) {
                Logger.debug('Tab became visible, refreshing data...');
                this._refreshOnVisible();
            }
        });

        // Handle online/offline
        this._addEventListener(window, 'online', () => {
            Logger.info('Connection restored');
            EventBus.emit('app:online');
            this._onConnectionRestored();
        });

        this._addEventListener(window, 'offline', () => {
            Logger.warn('Connection lost');
            EventBus.emit('app:offline');
            this._onConnectionLost();
        });

        // Handle errors globally
        this._addEventListener(window, 'error', (event) => {
            Logger.error('Global error:', event.error);
            EventBus.emit('app:error', { error: event.error });
        });

        // Handle unhandled promise rejections
        this._addEventListener(window, 'unhandledrejection', (event) => {
            Logger.error('Unhandled promise rejection:', event.reason);
            EventBus.emit('app:error', { error: event.reason });
        });

        // Before unload warning if unsaved changes
        this._addEventListener(window, 'beforeunload', (e) => {
            if (this._hasUnsavedChanges()) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        Logger.log(`✓ ${this.eventListeners.length} event listeners registered`);
    }

    /**
     * Track event listener for cleanup
     * @private
     */
    _addEventListener(target, event, handler, options) {
        target.addEventListener(event, handler, options);
        this.eventListeners.push({ target, event, handler, options });
    }

    /**
     * Post-initialization tasks
     * @private
     */
    _postInit() {
        Logger.log('Running post-initialization tasks...');

        // Set ready timestamp
        this.metrics.readyTime = Date.now();

        // Log performance metrics
        this._logPerformanceMetrics();

        // Setup periodic tasks
        this._setupPeriodicTasks();

        // Restore previous state if any
        this._restoreState();
    }

    /**
     * Check browser support
     * @private
     */
    _checkBrowserSupport() {
        const required = [
            'fetch',
            'Promise',
            'Map',
            'Set',
            'FormData',
            'URLSearchParams'
        ];

        const missing = required.filter(feature => !(feature in window));

        if (missing.length > 0) {
            Logger.error('Missing required features:', missing);
            return false;
        }

        return true;
    }

    /**
     * Check required DOM elements
     * @private
     */
    _checkRequiredElements() {
        const required = [
            '.dashboard-container',
            '.category-card'
        ];

        const missing = required.filter(selector => !document.querySelector(selector));

        if (missing.length > 0) {
            Logger.warn('Missing required elements:', missing);
            return false;
        }

        return true;
    }

    /**
     * Clear stale storage data
     * @private
     */
    _clearStaleStorage() {
        try {
            // Clear old data (older than 7 days)
            const staleThreshold = 7 * 24 * 60 * 60 * 1000; // 7 days
            const now = Date.now();

            // Check localStorage for stale entries
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith('ticket_')) {
                    try {
                        const data = JSON.parse(localStorage.getItem(key));
                        if (data.timestamp && (now - data.timestamp) > staleThreshold) {
                            localStorage.removeItem(key);
                            Logger.debug(`Removed stale storage: ${key}`);
                        }
                    } catch (e) {
                        // Invalid data, remove it
                        localStorage.removeItem(key);
                    }
                }
            });
        } catch (error) {
            Logger.warn('Failed to clear stale storage:', error);
        }
    }

    /**
     * Setup periodic tasks
     * @private
     */
    _setupPeriodicTasks() {
        // Refresh statistics every 5 minutes
        setInterval(() => {
            if (!document.hidden) {
                Logger.debug('Periodic statistics refresh');
                StatisticsManager.refresh();
            }
        }, 5 * 60 * 1000);

        // Log storage stats every 10 minutes
        setInterval(() => {
            const stats = StorageService.getStats();
            Logger.debug('Storage stats:', stats);

            if (stats.usagePercent > 80) {
                Logger.warn('Storage usage high:', stats);
            }
        }, 10 * 60 * 1000);
    }

    /**
     * Restore previous state
     * @private
     */
    _restoreState() {
        try {
            const savedState = localStorage.getItem('ticket_app_state');
            if (savedState) {
                const state = JSON.parse(savedState);
                Logger.debug('Restoring state:', state);
                
                // Restore active card if any
                if (state.activeCard) {
                    const card = document.querySelector(`[data-category="${state.activeCard}"]`);
                    if (card) {
                        setTimeout(() => CardExpansion.expand(card), 500);
                    }
                }
            }
        } catch (error) {
            Logger.warn('Failed to restore state:', error);
        }
    }

    /**
     * Save current state
     * @private
     */
    _saveState() {
        try {
            const state = {
                activeCard: CardExpansion.getActiveCategory(),
                timestamp: Date.now()
            };

            localStorage.setItem('ticket_app_state', JSON.stringify(state));
        } catch (error) {
            Logger.warn('Failed to save state:', error);
        }
    }

    /**
     * Check for unsaved changes
     * @private
     */
    _hasUnsavedChanges() {
        // Check if any form has unsaved data
        const forms = document.querySelectorAll('.ticket-form');
        
        for (const form of forms) {
            const formData = new FormData(form);
            let hasData = false;

            for (const [key, value] of formData.entries()) {
                if (value && value.toString().trim() && !key.startsWith('csrf_')) {
                    hasData = true;
                    break;
                }
            }

            if (hasData) return true;
        }

        // Check if any files are pending
        const stats = StorageService.getStats();
        return stats.totalFiles > 0;
    }

    /**
     * Handle tab becoming visible
     * @private
     */
    _refreshOnVisible() {
        // Only refresh if more than 5 minutes since last refresh
        const lastRefresh = this._lastRefreshTime || 0;
        const now = Date.now();
        
        if (now - lastRefresh > 5 * 60 * 1000) {
            StatisticsManager.refresh();
            this._lastRefreshTime = now;
        }
    }

    /**
     * Handle connection restored
     * @private
     */
    _onConnectionRestored() {
        // Refresh data when coming back online
        StatisticsManager.refresh();
    }

    /**
     * Handle connection lost
     * @private
     */
    _onConnectionLost() {
        // Could show offline indicator
        Logger.warn('Working in offline mode');
    }

    /**
     * Log performance metrics
     * @private
     */
    _logPerformanceMetrics() {
        try {
            if (!window.performance || !window.performance.timing) {
                return;
            }

            const timing = window.performance.timing;
            const navigationStart = timing.navigationStart || 0;

            const metrics = {
                domReady: timing.domContentLoadedEventEnd 
                    ? timing.domContentLoadedEventEnd - navigationStart 
                    : null,
                windowLoad: timing.loadEventEnd 
                    ? timing.loadEventEnd - navigationStart 
                    : null,
                appInit: this.metrics.initTime || 0
            };

            Logger.group('📊 Performance Metrics');
            
            if (metrics.domReady) {
                Logger.log('DOM Ready:', `${metrics.domReady}ms`);
            }
            
            if (metrics.windowLoad) {
                Logger.log('Window Load:', `${metrics.windowLoad}ms`);
            }
            
            Logger.log('App Init:', `${metrics.appInit.toFixed(2)}ms`);
            Logger.groupEnd();
        } catch (error) {
            Logger.warn('Failed to log performance metrics:', error);
        }
    }

    /**
     * Show initialization error
     * @private
     */
    _showInitError(error) {
        const message = `
            <div style="padding: 20px; background: #fee; border: 2px solid #c00; border-radius: 8px; margin: 20px; font-family: sans-serif;">
                <h3 style="margin: 0 0 10px 0; color: #c00;">Initialization Error</h3>
                <p style="margin: 0;">Failed to initialize the ticket system. Please refresh the page.</p>
                <details style="margin-top: 10px;">
                    <summary style="cursor: pointer;">Technical details</summary>
                    <pre style="margin: 10px 0 0 0; padding: 10px; background: #fff; border-radius: 4px; overflow: auto;">${error.message}\n${error.stack}</pre>
                </details>
            </div>
        `;

        const container = document.querySelector('.dashboard-container');
        if (container) {
            container.insertAdjacentHTML('afterbegin', message);
        }
    }

    /**
     * Destroy and cleanup the application
     */
    destroy() {
        if (!this.initialized) {
            Logger.warn('App not initialized, nothing to destroy');
            return;
        }

        Logger.log('Destroying Ticket App...');

        // Save state before destroying
        this._saveState();

        // Destroy components
        this.components.forEach((component, name) => {
            try {
                if (typeof component.destroy === 'function') {
                    component.destroy();
                } else if (typeof component.stop === 'function') {
                    component.stop();
                }
                Logger.log(`✓ ${name} destroyed`);
            } catch (error) {
                Logger.error(`Failed to destroy ${name}:`, error);
            }
        });

        // Remove event listeners
        this.eventListeners.forEach(({ target, event, handler, options }) => {
            target.removeEventListener(event, handler, options);
        });

        // Clear storage
        FileUploadManager.destroy?.();

        // Reset state
        this.initialized = false;
        this.components.clear();
        this.eventListeners = [];

        Logger.log('App destroyed');
        EventBus.emit('app:destroyed');
    }

    /**
     * Restart the application
     */
    async restart() {
        Logger.log('Restarting app...');
        this.destroy();
        await new Promise(resolve => setTimeout(resolve, 100));
        return await this.init();
    }

    /**
     * Get app status
     */
    getStatus() {
        return {
            initialized: this.initialized,
            version: this.version,
            metrics: this.metrics,
            components: Array.from(this.components.keys()),
            storage: StorageService.getStats()
        };
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

window.__TICKET_DEBUG__ = {
    app,
    EventBus,
    Logger,
    StorageService,
    StatisticsManager,
    getStatus: () => app.getStatus()
};

export default app;