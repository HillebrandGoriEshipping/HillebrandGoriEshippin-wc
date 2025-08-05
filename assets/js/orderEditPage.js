import apiClient from './apiClient.js';
const __ = wp.i18n.__;

const orderEditPage = {
    changeShippingRateButton: null,
    closeShippingRateModalButton: null,
    updateShippingRateButton: null,
    currentEditingItemId: null,
    selectedShippingRateChecksum: null,
    currentAttachments: [],
    async init() {
        document.querySelectorAll('.filepond-file-input').forEach((fileInput) => {
            const fileType = fileInput.dataset.fileType;
            const fileLabel = fileInput.dataset.fileLabel || 'Attachment';
            FilePond.create(fileInput, {
                allowMultiple: false,
                credits: false,
                labelIdle: __('Click or drop a file to upload'),
                server: {
                    process: async (fieldName, file, metadata, load, error, progress, abort) => {
                        const response = await apiClient.upload(window.hges.apiUrl + '/v2/attachments/upload', {}, {file, type: fileType});
                        if (response.error) {
                            error(response.error);
                        }
                        if (response.progress) {
                            progress(response.progress);
                        } else {
                            load(response.id);

                        }
                        return {
                            abort: () => {
                                request.abort();
                                abort();
                            },
                        };
                    },
                },
                onprocessfile: (error, file) => {
                    file.setMetadata('fileType', fileType);
                    file.setMetadata('label', fileLabel);
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
    
    
    fileUploadedError(error, file) {
        console.error('File upload error:', error, file);
    },
    async fileUploadedSuccess(file) {
        if (this.currentAttachments.some(attachment => attachment.type === file.getMetadata('fileType'))) {
            this.currentAttachments = this.currentAttachments.filter(attachment => attachment.type !== file.getMetadata('fileType'));
        }

        this.currentAttachments.push({
            id: file.serverId,
            name: file.filename,
            url: file.serverUrl,
            mimeType: file.fileType,
            type: file.getMetadata('fileType'),
            label: file.getMetadata('label')
        });

        document.querySelector('#attachments-marker-' + file.getMetadata('fileType')).classList.remove('marker-red');
        document.querySelector('#attachments-marker-' + file.getMetadata('fileType')).classList.remove('dashicons-marker');
        document.querySelector('#attachments-marker-' + file.getMetadata('fileType')).classList.add('marker-green');
        document.querySelector('#attachments-marker-' + file.getMetadata('fileType')).classList.add('dashicons-yes-alt');

        try {
            const response = await apiClient.post(
                window.hges.ajaxUrl, 
                {
                    action: 'hges_update_order_attachments',
                },
                {
                    orderId: new URLSearchParams(window.location.search).get('id'),
                    attachments: this.currentAttachments,
                },
            );
        } catch (error) {
            console.error('Attachments update failed', error);
        }
    },
};

document.addEventListener('DOMContentLoaded', orderEditPage.init.bind(orderEditPage));