const __ = wp.i18n.__;
import { useState, useEffect } from 'react';
import apiClient from '../../../../../apiClient';
import ShippingRateItem from './ShippingRateItem';

const ShippingRateModal = ({ isOpen, onClose, validateShippingRate }) => {

    const [rates, setRates] = useState("");
    const [selectedRateChecksum, setSelectedRateChecksum] = useState("");

    const loadShippingRates = async () => {
        const currentUrlParams = new URLSearchParams(window.location.search);
        const response = await apiClient.get(window.hges.ajaxUrl, { orderId: currentUrlParams.get('id'), action: 'hges_get_shipping_rates_for_order' });
        console.log("Shipping rates response:", response);
        setRates(response.shippingRates);
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
                        ) : (
                            <p>{__('No shipping rates available.', 'hges')}</p>
                        )}

                    </div>
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
        </div>
    );
};

export default ShippingRateModal;