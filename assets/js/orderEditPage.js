import apiClient from './apiClient.js';

const orderEditPage = {
    changeShippingRateButton: null,
    closeShippingRateModalButton: null,
    updateShippingRateButton: null,
    currentEditingItemId: null,
    selectedShippingRateChecksum: null,
    init() {

        document.querySelectorAll('.filepond-file-input').forEach((fileInput) => {
            FilePond.create(fileInput, {
                allowMultiple: true,
                server: '?action=hges_upload_documents',
                onprocessfile: (error, file) => {
                    if (error) {
                        this.fileUploadedError(error, file);
                    } else {
                        this.fileUploadedSuccess(file);
                    }
                }
            });
        });

        this.changeShippingRateButton = document.querySelector('#hges-change-shipping-rate-button');
        this.closeShippingRateModalButton = document.querySelector('#hges-shipping-rate-modal .modal__close');
        this.updateShippingRateButton = document.querySelector('#hges-close-shipping-rate-modal-button');

        if (this.changeShippingRateButton) {
            this.changeShippingRateButton.addEventListener('click', this.openShippingRateModal.bind(this));
        }
        if (this.closeShippingRateModalButton) {
            this.closeShippingRateModalButton.addEventListener('click', this.closeShippingRateModal.bind(this));
        }
        if (this.updateShippingRateButton) {
            this.updateShippingRateButton.addEventListener('click', this.updateShippingRate.bind(this));
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
        document.querySelectorAll('#hges-shipping-rate-modal .shipping-rate-list .hges-shipping-method').forEach((rateElement) => {
            rateElement.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        this.selectedShippingRateChecksum = event.target.dataset.checksum;
    },
    async updateShippingRate() {
        if (this.selectedShippingRateChecksum) {
            console.log('Selected shipping rate checksum:', this.selectedShippingRateChecksum);
            this.selectedRate = await apiClient.patch('/order/set-shipping-rate', {
                orderId: new URLSearchParams(window.location.search).get('id'),
                orderShippingItemId: this.currentEditingItemId,
            }, {
                shippingRateChecksum: this.selectedShippingRateChecksum
            },
                {},
                true
            );

            window.location.reload();
        }
    },
    fileUploadedError(error, file) {
        console.error('File upload error:', error, file);
    },
    async fileUploadedSuccess(file) {
        try {
            await jQuery.ajax('/wp/wp-admin/admin-ajax.php?action=hges_update_order_documents', {
                method: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    orderId: new URLSearchParams(window.location.search).get('id'),
                    documents: [file],
                }),
            });
            console.log('Documents updated', response);
        } catch (error) {
            console.error('Documents update failed', response);
        }
    }
};

document.addEventListener('DOMContentLoaded', orderEditPage.init.bind(orderEditPage));