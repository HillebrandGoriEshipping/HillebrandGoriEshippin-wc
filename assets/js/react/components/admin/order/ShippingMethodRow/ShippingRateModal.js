const __ = wp.i18n.__;
import { useState, useEffect } from 'react';
import apiClient from '../../../../../apiClient';
import ShippingRateItem from './ShippingRateItem';
import LoadingMask from '../../../../../blocks/LoadingMask';
import PickupPointsMap from '../../../../../blocks/PickupPointsMap';

const ShippingRateModal = ({ isOpen, onClose, validateShippingRate }) => {

    const [rates, setRates] = useState("");
    const [selectedRateChecksum, setSelectedRateChecksum] = useState("");
    const [loading, setLoading] = useState(true);

    const loadShippingRates = async () => {
        setLoading(true);
        const currentUrlParams = new URLSearchParams(window.location.search);
        const response = await apiClient.get(window.hges.ajaxUrl, { orderId: currentUrlParams.get('id'), action: 'hges_get_shipping_rates_for_order' });
        setRates(response.shippingRates);
        setLoading(false);
    };

    useEffect(() => {
        if (!isOpen) {
            setRates([]);
            return;
        }
        loadShippingRates();
    }, [isOpen]);

    const onRateSelected = (checksum) => {
        setSelectedRateChecksum(checksum);
    }

    return (
        <div id="hges-shipping-rate-modal" className={`modal ${!isOpen ? "hidden" : ""}`}>
            <div className="modal__content">
                <span className="modal__close" onClick={onClose}>&times;</span>
                <div className="modal__header">
                    <h2 className="modal__title">
                        {__('Choose your shipping option')}
                    </h2>
                </div>
                <div className="modal__body">
                    <LoadingMask
                        isLoading={loading}
                        screenReaderLabel={__("Loading shipping rates", "hges")}
                        showSpinner={true}
                    >
                    <div className="shipping-rate-list">
                        {rates && rates.length > 0 ? (
                            rates.map((rate) => (
                                <ShippingRateItem
                                    key={rate.checksum}
                                    rate={rate}
                                    onSelect={onRateSelected}
                                    isSelected={selectedRateChecksum === rate.checksum}
                                />
                            ))
                        ) : loading ? '' : (
                            <p>{__('No shipping rates available.', 'hges')}</p>
                        )}

                    </div>
                    </LoadingMask>
                </div>
                <div className="modal__footer">
                    <button
                        type="button"
                        id="hges-update-shipping-rate-modal-button"
                        onClick={() => validateShippingRate(selectedRateChecksum)}
                    >
                        {__('Update')}
                    </button>
                    <div className="warning">
                        <p>
                            {__(
                                'Updating the shipping method will reload the page. Ensure that all your modification have been saved before performing this action !'
                            )}
                        </p>
                    </div>
                </div>
            </div>
            <PickupPointsMap />
        </div>
    );
};

export default ShippingRateModal;