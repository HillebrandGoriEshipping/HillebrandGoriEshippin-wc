const __ = wp.i18n.__;
import Attachments from './Attachments';

const ShippingRowBody = ({
    shippingRate,
    attachments = [],
    remainingAttachments = [],
}) => {
    const requiredAttachments = shippingRate?.requiredAttachments || [];
    const priceDelta = shippingRate?.meta_data?.priceDelta;

    return (
        <div>
            <div className="shipping-rate-row">
                <div className="shipping-rate-service-name">{shippingRate.serviceName}</div>
                {typeof priceDelta !== "undefined" && (
                    <div className="shipping-rate-price-delta">
                        <span className="marker-red dashicons dashicons-warning"></span>
                        { translate('Price change:') } {priceDelta > 0 ? "+" : ""}
                        {priceDelta}
                    </div>
                ) }
            </div>
            <div className="shipping-rate-estimated-delivery">
                { translate('Estimated delivery date :') } {shippingRate.deliveryDate}
            </div>
            <div className="shipping-rate-pickup-date">
                { translate('Pickup date :') } {shippingRate.pickupDate}
            </div>
            
            {requiredAttachments.length > 0 && (
                <Attachments
                    attachments={attachments}
                    requiredAttachments={requiredAttachments}
                    remainingAttachments={remainingAttachments}
                />
            ) }

        </div>
    );
};

export default ShippingRowBody;