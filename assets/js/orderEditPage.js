import apiClient from './apiClient.js';

const orderEditPage = {
    changeShippingRateButton: null,
    closeShippingRateModalButton: null,
    updateShippingRateButton: null,
    currentEditingItemId: null,
    selectedShippingRateChecksum: null,
    currentDocuments: [],
    init() {
        this.loadDocumentList();
        document.querySelectorAll('.filepond-file-input').forEach((fileInput) => {
            const fileType = fileInput.dataset.fileType;
            FilePond.create(fileInput, {
                allowMultiple: false,
                server: {
                    process: async (fieldName, file, metadata, load, error, progress, abort) => {
                        
                        const response = await apiClient.upload(window.hges.apiUrl + '/v2/attachments/upload', {}, {file, type: fileType});
                        if (response.error) {
                            error(response.error);
                        }
                        if (response.progress) {
                            progress(response.progress);
                        }
                        console.log('File uploaded:', response);
                        load(response.id);

                        return {
                            abort: () => {
                                // This function is entered if the user has tapped the cancel button
                                request.abort();

                                // Let FilePond know the request has been cancelled
                                abort();
                            },
                        };
                    },
                },
                onprocessfile: (error, file) => {
                    console.log('File processed:', file);
                    console.log('metadata:', file.getMetadata());
                    
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
        this.updateShippingRateButton = document.querySelector('#hges-update-shipping-rate-modal-button');

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
        this.selectedShippingRateChecksum = event.currentTarget.dataset.checksum;
    },
    async updateShippingRate() {
        if (this.selectedShippingRateChecksum) {
            this.selectedRate = await apiClient.patch(
                '/order/set-shipping-rate', 
                {
                    orderId: new URLSearchParams(window.location.search).get('id'),
                    orderShippingItemId: this.currentEditingItemId,
                }, 
                {
                    shippingRateChecksum: this.selectedShippingRateChecksum
                },
                {},
                true
            );
            
            // window.location.reload();
        }
    },
    fileUploadedError(error, file) {
        console.error('File upload error:', error, file);
    },
    async fileUploadedSuccess(file) {
        console.log('File uploaded successfully:', file);
        this.currentDocuments.push({
            id: file.serverId,
            name: file.filename,
            url: file.serverUrl,
            mimeType: file.fileType,
            type: file.type,
        });
        try {
            const response = await apiClient.post(
                window.hges.ajaxUrl, 
                {
                    action: 'hges_update_order_documents',
                },
                {
                    orderId: new URLSearchParams(window.location.search).get('id'),
                    documents: this.currentDocuments,
                },
            );
            console.log('Documents updated', response);
        } catch (error) {
            console.error('Documents update failed', error);
        }
    },
    async loadDocumentList() {
        const orderId = new URLSearchParams(window.location.search).get('id');
        try {
            const documents = await apiClient.get(
                window.hges.ajaxUrl, 
                { action: 'hges_get_documents_list', orderId }
            );
            this.currentDocuments = documents || [];
            console.log('Documents loaded:', this.currentDocuments);
        } catch (error) {
            console.error('Failed to load documents:', error);
        }
    }
};

document.addEventListener('DOMContentLoaded', orderEditPage.init.bind(orderEditPage));