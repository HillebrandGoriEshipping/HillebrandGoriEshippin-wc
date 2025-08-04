const __ = wp.i18n.__;
import apiClient from '../../../../../apiClient';
import ShippingRateModal from './ShippingRateModal';
import ShippingRowBody from './ShippingRowBody';
import { useState } from 'react';

const ShippingMethodRow = ({
    errorMessage,
    stillAvailable,
    shippingRate,
    attachments = [],
    remainingAttachments = [],
    itemId,
}) => {
    const [isRateSelectionModalOpen, setIsRateSelectionModalOpen] = useState(false);

    const openShippingRateModal = async (e) =>{
        e.preventDefault();
        setIsRateSelectionModalOpen(true);
    }

    const closeShippingRateModal = () =>{
        setIsRateSelectionModalOpen(false);
    }

    const onShippingRateValidated = async (selectedShippingRateChecksum) => {
        const selectedRate = await apiClient.post(
            window.hges.ajaxUrl, 
            {
                action: 'hges_set_order_shipping_rate',
                orderId: new URLSearchParams(window.location.search).get('id'),
                orderShippingItemId: itemId,
            }, 
            {
                shippingRateChecksum: selectedShippingRateChecksum
            },
        );
        
        window.location.reload();
    }

    const render = () => {
        return (
            <div className="shipping-method-row">
                <div className={`error-message ${stillAvailable ? "hidden" : ""}`}>
                    {errorMessage}
                </div>
                {!shippingRate ? '' : (
                    <ShippingRowBody
                        shippingRate={shippingRate}
                        attachments={attachments}
                        remainingAttachments={remainingAttachments}
                        itemId={itemId}
                    />
                )}
                <button
                    type="button"
                    id="hges-change-shipping-rate-button"
                    data-item-id={itemId}
                    onClick={openShippingRateModal}
                >
                    {__('Change shipping option')}
                </button>
                <ShippingRateModal
                    isOpen={isRateSelectionModalOpen}
                    onClose={closeShippingRateModal}
                    validateShippingRate={onShippingRateValidated}
                />
            </div>
        );
    };
    return render();
};

export default ShippingMethodRow;
