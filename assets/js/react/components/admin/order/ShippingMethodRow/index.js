const __ = wp.i18n.__;
import apiClient from '../../../../../apiClient';
import ShippingRateModal from './ShippingRateModal';
import ShippingRowBody from './ShippingRowBody';
import { useState, useEffect } from 'react';
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

    const [shipmentError, setShipmentError] = useState("");

    const onValidateShipment = async () => {
       try {
        const response = await apiClient.post(
            window.hges.ajaxUrl,
            {
                action: 'hges_create_shipment',
                orderId: new URLSearchParams(window.location.search).get('id'),
            }
        );

        if (response.error) {
            setShipmentError(response.error);
        } else {
            setShipmentError("");
            window.location.reload();
        }

        } catch (error) {
            console.error("error:", error);
            setShipmentError("Impossible de valider l’expédition, erreur réseau.");
        }
    }

    const [hasShipment, setHasShipment] = useState(false);

    useEffect(() => {
        const orderId = new URLSearchParams(window.location.search).get("id");
        if (!orderId) return;

        checkIfHasShipment(orderId).then((result) => {
            setHasShipment(result);
            setLoading(false);
        });
    }, []);


    const checkIfHasShipment = async (orderId) => {
        try {
            const res = await apiClient.post(
            window.hges.ajaxUrl, 
                {
                    action: 'hges_check_if_has_shipment',
                    orderId: orderId,
                }
            );

            if (res.success) {
                return res.hasShipment;
            }
            return false;
        } catch (err) {
            console.error("Error checking shipment:", err);
            return false;
        }
    }

    const render = () => {
        return (
            <div className="shipping-method-row">
                <div className={`error-message ${stillAvailable ? "hidden" : ""}`}>
                    {errorMessage}
                </div>

                <Packaging products={products} packaging={packaging} onPackagingUpdated={onPackagingUpdated} />

                {hasShipment ? '' :(
                <button
                    type="button"
                    id="hges-change-shipping-rate-button"
                    data-item-id={itemId}
                    onClick={openShippingRateModal}
                >
                    {__('Change shipping option')}
                </button>
                )}

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

                {hasShipment ? (
                    <div className="shipment-validated-message">
                        {__('Shipment has been validated for this order.')}
                    </div>
                ) : (
                    <button
                        type="button"
                        id="hges-validate-shipment-button"
                        onClick={onValidateShipment}
                    >
                        {__('Validate shipment')}
                    </button>
                )}
                {shipmentError && (
                    <div className="shipment-error">
                        {shipmentError}
                    </div>
                )}
            </div>
        );
    };
    return render();
};

export default ShippingMethodRow;
