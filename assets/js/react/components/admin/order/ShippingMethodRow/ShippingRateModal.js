const __ = wp.i18n.__;
import { useState } from 'react';
import apiClient from '../../../../../apiClient';

const ShippingRateModal = ({ isOpen, onClose, onShippingRateSelected }) => {

    const [ratesHTML, setRatesHTML] = useState("");

    const loadShippingRates = async () => {
        const currentUrlParams = new URLSearchParams(window.location.search);
        const response = await apiClient.get(window.hges.ajaxUrl, { orderId: currentUrlParams.get('id'), action: 'hges_get_shipping_rates_for_order_html' });
        
        setRatesHTML(response.shippingRatesHtml);
    };

    React.useEffect(() => {
        loadShippingRates();
    }, []);

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
                    <div className="shipping-rate-list" dangerouslySetInnerHTML={{ __html: ratesHTML }}></div>
                </div>
                <div className="modal__footer">
                    <button
                        type="button"
                        id="hges-update-shipping-rate-modal-button"
                        onClick={onShippingRateSelected}
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