import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['panel', 'button'];
    static values = { open: { type: Boolean, default: false } };

    open() { this.openValue = true; }
    close() { this.openValue = false; }

    openValueChanged() {
        this.panelTarget.dataset.open = this.openValue;
        this.buttonTarget.setAttribute('aria-expanded', this.openValue);
        document.body.classList.toggle('overflow-hidden', this.openValue);
    }

    disconnect() {
        document.body.classList.remove('overflow-hidden');
    }
}