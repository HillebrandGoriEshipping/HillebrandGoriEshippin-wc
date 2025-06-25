import apiClient from './apiClient.js';

const orderEditPage = {
    changeShippingRateButton: null,
    closeShippingRateModalButton: null,
    init() {
        this.changeShippingRateButton = document.querySelector('#hges-change-shipping-rate-button');
        this.closeShippingRateModalButton = document.querySelector('#hges-shipping-rate-modal .modal__close');
        if (this.changeShippingRateButton) {
            this.changeShippingRateButton.addEventListener('click', this.openShippingRateModal);
        }
        if (this.closeShippingRateModalButton) {
            this.closeShippingRateModalButton.addEventListener('click', this.closeShippingRateModal);
        }
    },
    async openShippingRateModal() {
        const modal = document.querySelector('#hges-shipping-rate-modal');
        if (modal) {
            modal.classList.remove('hidden');
            const currentUrlParams = new URLSearchParams(window.location.search);
            const response = await apiClient.get('/shipping-rates', {orderId: currentUrlParams.get('id')}, {}, true);
            document.querySelector('#hges-shipping-rate-modal .shipping-rate-list').innerHTML = response.shippingRatesHtml;
        }
    },
    closeShippingRateModal() {
        const modal = document.querySelector('#hges-shipping-rate-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
};

document.addEventListener('DOMContentLoaded', orderEditPage.init.bind(orderEditPage));