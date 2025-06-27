import apiClient from './apiClient.js';

const orderEditPage = {
    changeShippingRateButton: null,
    closeShippingRateModalButton: null,
    currentEditingItemId: null,
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
    async openShippingRateModal(e) {
        this.currentEditingItemId = e.currentTarget.dataset.itemId;
        console.log('Opening shipping rate modal for order ID:', this.currentEditingItemId);

        const modal = document.querySelector('#hges-shipping-rate-modal');
        if (modal) {
            modal.classList.remove('hidden');
            const currentUrlParams = new URLSearchParams(window.location.search);
            const response = await apiClient.get('/shipping-rates', { orderId: currentUrlParams.get('id') }, {}, true);
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
    async onShippingRateSelected(event) {
        const shippingRateChecksum = event.target.dataset.checksum;
        if (shippingRateChecksum) {
            console.log('Selected shipping rate checksum:', shippingRateChecksum);
            const selectedRate = await apiClient.patch('/order/set-shipping-rate', {
                orderId: new URLSearchParams(window.location.search).get('id'),
                orderShippingItemId: this.currentEditingItemId,
            }, {
                shippingRateChecksum: shippingRateChecksum
            },
                {},
                true
            );
            console.log(selectedRate);
            this.closeShippingRateModal();
        }
    }
};

document.addEventListener('DOMContentLoaded', orderEditPage.init.bind(orderEditPage));