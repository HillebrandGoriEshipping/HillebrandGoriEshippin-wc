import apiClient from './apiClient.js';

const orderEditPage = {
    changeShippingRateButton: null,
    closeShippingRateModalButton: null,
    init() {
        this.changeShippingRateButton = document.querySelector('#hges-change-shipping-rate-button');
        this.closeShippingRateModalButton = document.querySelector('#hges-shipping-rate-modal .modal__close');
        if (this.changeShippingRateButton) {
            this.changeShippingRateButton.addEventListener('click', this.openShippingRateModal.bind(this));
        }
        if (this.closeShippingRateModalButton) {
            this.closeShippingRateModalButton.addEventListener('click', this.closeShippingRateModal.bind(this));
        }
    },
    async openShippingRateModal() {
        const modal = document.querySelector('#hges-shipping-rate-modal');
        if (modal) {
            modal.classList.remove('hidden');
            const currentUrlParams = new URLSearchParams(window.location.search);
            const response = await apiClient.get('/shipping-rates', {orderId: currentUrlParams.get('id')}, {}, true);
            document.querySelector('#hges-shipping-rate-modal .shipping-rate-list').innerHTML = response.shippingRatesHtml;
            document.querySelectorAll('#hges-shipping-rate-modal .shipping-rate-list .hges-shipping-method').forEach((rateElement) => {
                rateElement.addEventListener('click', this.onShippingRateSelected.bind(this));
            });
        }
    },
    closeShippingRateModal() {
        const modal = document.querySelector('#hges-shipping-rate-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    },
    onShippingRateSelected(event) {
        const shippingRateChecksum = event.target.dataset.checksum;
        if (shippingRateChecksum) {
            console.log('Selected shipping rate checksum:', shippingRateChecksum);
        }
    }
};

document.addEventListener('DOMContentLoaded', orderEditPage.init.bind(orderEditPage));