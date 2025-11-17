import { CONFIG }           from '../config/constants.js';
import { formatTimestamp }  from '../utils/format.js';
import { $$ }               from '../utils/dom.js';

class Timestamp {
    constructor() {
        this.intervalId = null;
    }

    start() {
        this.update();
        this.intervalId = setInterval(() => this.update(), CONFIG.UI.TIMESTAMP_INTERVAL);
    }

    stop() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    update() {
        const timeString = formatTimestamp();
        $$('.timestamp').forEach(el => {
            el.textContent = `Time: ${timeString}`;
        });
    }
}

export default new Timestamp();