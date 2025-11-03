import StateManager from '../core/StateManager.js';
import EventBus from '../core/EventBus.js';
import { $, $$, addClass, removeClass } from '../utils/dom.js';

class CardExpansion {
    constructor() {
        this.container = $('.dashboard-container');
        this.cards = $$('.category-card');
        this.navPills = $$('.nav-pill');
    }

    init() {
        this._bindEvents();
    }

    expand(card) {
        // Collapse others
        this.cards.forEach(c => {
            if (c !== card) removeClass(c, 'expanded');
        });

        addClass(card, 'expanded');
        addClass(this.container, 'has-active');

        const category = card.dataset.category;
        StateManager.set('activeCard', category);

        // Update nav pills
        this.navPills.forEach(pill => {
            if (pill.dataset.target === category) {
                addClass(pill, 'active');
            } else {
                removeClass(pill, 'active');
            }
        });

        EventBus.emit('card:expanded', category);
    }

    collapse() {
        this.cards.forEach(card => removeClass(card, 'expanded'));
        removeClass(this.container, 'has-active');
        this.navPills.forEach(pill => removeClass(pill, 'active'));
        
        StateManager.set('activeCard', null);
        EventBus.emit('card:collapsed');
    }

    _bindEvents() {
        // Card click
        this.cards.forEach(card => {
            card.addEventListener('click', (e) => {
                if (e.target.closest('.close-btn') || e.target.closest('.card-body')) {
                    return;
                }
                this.expand(card);
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
            pill.addEventListener('click', () => {
                const target = pill.dataset.target;
                const targetCard = $(`.category-card[data-category="${target}"]`);
                if (targetCard) this.expand(targetCard);
            });
        });
    }
}

export default new CardExpansion();