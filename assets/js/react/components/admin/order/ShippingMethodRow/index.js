const __ = wp.i18n.__;
import apiClient from '../../../../../apiClient';
import ShippingRateModal from './ShippingRateModal';
import ShippingRowBody from './ShippingRowBody';
import { useState } from 'react';
import Packaging from './Packaging';

const ShippingMethodRow = ({
    errorMessage,
    stillAvailable,
    shippingRate,
    attachments = [],
    remainingAttachments = [],
    itemId,
    products = [],
    packaging = [],
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

    const onPackagingUpdated = () => {
        setIsRateSelectionModalOpen(true);
    }

    const onValidateShipment = async () => {
       try {
        const response = await apiClient.post(
            window.hges.ajaxUrl,
            {
                action: 'hges_create_shipment',
                orderId: new URLSearchParams(window.location.search).get('id'),
            }
        );

        } catch (error) {
            console.error("❌ AJAX error:", error);
        }
    }

    const render = () => {
        return (
            <div className="shipping-method-row">
                <div className={`error-message ${stillAvailable ? "hidden" : ""}`}>
                    {errorMessage}
                </div>

                <Packaging products={products} packaging={packaging} onPackagingUpdated={onPackagingUpdated} />

                <button
                    type="button"
                    id="hges-change-shipping-rate-button"
                    data-item-id={itemId}
                    onClick={openShippingRateModal}
                >
                    {__('Change shipping option')}
                </button>

                {!shippingRate ? '' : (
                    <ShippingRowBody
                        shippingRate={shippingRate}
                        attachments={attachments}
                        remainingAttachments={remainingAttachments}
                    />
                )}

                <ShippingRateModal
                    isOpen={isRateSelectionModalOpen}
                    onClose={closeShippingRateModal}
                    validateShippingRate={onShippingRateValidated}
                />

                <button
                    type="button"
                    id="hges-validate-shipment-button"
                    onClick={onValidateShipment}
                >
                    {__('Validate shipment')}
                </button>
            </div>
        );
    };
    return render();
};

export default ShippingMethodRow;
