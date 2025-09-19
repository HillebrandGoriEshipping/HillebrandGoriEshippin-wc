const __ = wp.i18n.__;
import apiClient from '../../../../../apiClient';
import ShippingRateModal from './ShippingRateModal';
import ShippingRowBody from './ShippingRowBody';
import { useState, useEffect } from 'react';
import Packaging from './Packaging';
import LoadingMask from '../../../../../blocks/LoadingMask';

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
    const [isLoadingShipment, setIsLoadingShipment] = useState(false);

    const onValidateShipment = async () => {
        setIsLoadingShipment(true);
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
        setShipmentError(__("An error occurred while validating the shipment."));
      } finally {
        setIsLoadingShipment(false);
      }
    }

    const [hasShipment, setHasShipment] = useState(false);

    useEffect(() => {
        const orderId = new URLSearchParams(window.location.search).get("id");
        if (!orderId) return;

        checkIfHasShipment(orderId).then((result) => {
            setHasShipment(result);
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
                <LoadingMask
                    isLoading={isLoadingShipment}
                    screenReaderLabel={__("Validating shipment...", "hges")}
                    showSpinner={true}
                >
                    <div className="shipping-method-row-inner">
                        <div className={`error-message ${stillAvailable ? "hidden" : ""}`}>
                            {errorMessage}
                        </div>
                        <div className="package-details">
                            {hasShipment ? '' : (
                                <Packaging products={products} packaging={packaging} onPackagingUpdated={onPackagingUpdated} />
                            )}
                           
                        </div>
                        <div className="shipping-details">
                            {!shippingRate ? '' : (
                                <ShippingRowBody
                                    shippingRate={shippingRate}
                                    attachments={attachments}
                                    remainingAttachments={remainingAttachments}
                                />
                            )}
                            {hasShipment ? '' : (
                                <button
                                    className='hges-button edit-order-button'
                                    type="button"
                                    id="hges-change-shipping-rate-button"
                                    data-item-id={itemId}
                                onClick={openShippingRateModal}
                                >
                                {__('Change shipping option')}
                                </button>
                            )}
                        </div>


                        <ShippingRateModal
                            isOpen={isRateSelectionModalOpen}
                            onClose={closeShippingRateModal}
                            validateShippingRate={onShippingRateValidated}
                        />

                    </div>
                    {hasShipment ? (
                        <div className="shipment-validated-message">
                            {__('Shipment has been validated for this order.')}
                        </div>
                    ) : (
                        <button
                            className="hges-button validate-shipment-button"
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
                </LoadingMask>
            </div>
        );
    };
    return render();
};

export default ShippingMethodRow;
