import StateManager from '../core/StateManager.js';
import EventBus from '../core/EventBus.js';
import { $, $$, addClass, removeClass } from '../utils/dom.js';
import Logger from '../utils/logger.js';

class CardExpansion {
    constructor() {
        this.container = null;
        this.cards = [];
        this.navPills = [];
        this.currentActiveCard = null;
        
        this._initElements();
    }

    /**
     * Initialize DOM elements
     * @private
     */
    _initElements() {
        this.container = $('.dashboard-container');
        this.cards = $$('.category-card');
        this.navPills = $$('.nav-pill');

        if (!this.container) {
            Logger.warn('Dashboard container not found');
        }

        if (this.cards.length === 0) {
            Logger.warn('No category cards found');
        }
    }

    init() {
        this._bindEvents();
        this._restoreState();
    }

    /**
     * Restore previous state if any
     * @private
     */
    _restoreState() {
        const activeCard = StateManager.get('activeCard');
        if (activeCard) {
            const card = $(`.category-card[data-category="${activeCard}"]`);
            if (card) {
                this.expand(card);
            }
        }
    }

    /**
     * Expand a card
     * @param {HTMLElement} card - Card element to expand
     */
    expand(card) {
        if (!card) {
            Logger.error('Card element is null');
            return;
        }

        const category = card.dataset.category;
        const previousCategory = this.currentActiveCard;

        // Collapse others first
        this.cards.forEach(c => {
            if (c !== card) {
                removeClass(c, 'expanded');
            }
        });

        // Expand target card
        addClass(card, 'expanded');
        addClass(this.container, 'has-active');

        // Update state
        StateManager.set('activeCard', category);
        this.currentActiveCard = category;

        // Update nav pills
        this.navPills.forEach(pill => {
            if (pill.dataset.target === category) {
                addClass(pill, 'active');
            } else {
                removeClass(pill, 'active');
            }
        });

        // Emit events for form switching
        if (previousCategory && previousCategory !== category) {
            // Switching from one form to another
            EventBus.emit('form:switch', { 
                fromFormId: previousCategory, 
                toFormId: category 
            });
        }

        // Emit card expanded event with correct format
        EventBus.emit('card:expanded', { category });

        Logger.info('Card expanded', { category, previousCategory });
    }

    /**
     * Collapse all cards
     */
    collapse() {
        const previousCategory = this.currentActiveCard;

        // Collapse all cards
        this.cards.forEach(card => removeClass(card, 'expanded'));
        removeClass(this.container, 'has-active');
        
        // Reset nav pills
        this.navPills.forEach(pill => removeClass(pill, 'active'));
        
        // Update state
        StateManager.set('activeCard', null);
        this.currentActiveCard = null;

        // Emit collapse event
        EventBus.emit('card:collapsed', { previousCategory });

        // Also emit form:switch to trigger reset of previous form
        if (previousCategory) {
            EventBus.emit('form:switch', { 
                fromFormId: previousCategory, 
                toFormId: null 
            });
        }

        Logger.info('Card collapsed', { previousCategory });
    }

    /**
     * Get currently active card category
     * @returns {string|null}
     */
    getActiveCategory() {
        return this.currentActiveCard;
    }

    /**
     * Check if a card is expanded
     * @param {string} category - Category to check
     * @returns {boolean}
     */
    isExpanded(category) {
        return this.currentActiveCard === category;
    }

    /**
     * Bind all events
     * @private
     */
    _bindEvents() {
        // Card click to expand
        this.cards.forEach(card => {
            card.addEventListener('click', (e) => {
                // Don't expand if clicking on close button or inside card body
                if (e.target.closest('.close-btn') || 
                    e.target.closest('.card-body') ||
                    e.target.closest('form')) {
                    return;
                }
                
                // Toggle expansion
                if (card.classList.contains('expanded')) {
                    this.collapse();
                } else {
                    this.expand(card);
                }
            });
        });

        // Close buttons
        $$('.close-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.collapse();
            });
        });

        // Nav pills
        this.navPills.forEach(pill => {
            pill.addEventListener('click', (e) => {
                e.preventDefault();
                
                const target = pill.dataset.target;
                const targetCard = $(`.category-card[data-category="${target}"]`);
                
                if (targetCard) {
                    // If clicking on already active pill, collapse
                    if (this.currentActiveCard === target) {
                        this.collapse();
                    } else {
                        this.expand(targetCard);
                    }
                }
            });
        });

        // Listen to card:collapse event from other components
        EventBus.on('card:collapse', () => {
            this.collapse();
        });

        // Listen to ESC key to collapse
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.currentActiveCard) {
                this.collapse();
            }
        });
    }

    /**
     * Refresh elements (useful after dynamic content load)
     */
    refresh() {
        this._initElements();
        this._bindEvents();
    }
}

export default new CardExpansion();