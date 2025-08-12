const __ = wp.i18n.__;
import Attachments from './Attachments';
import Packaging from './Packaging';

const ShippingRowBody = ({
    shippingRate,
    attachments = [],
    remainingAttachments = [],
    itemId,
    products = [],
}) => {
    const requiredAttachments = shippingRate?.requiredAttachments || [];
    const priceDelta = shippingRate?.meta_data?.priceDelta;

    return (
        <div>
            <div className="shipping-rate-row">
                <div className="shipping-rate-price">{shippingRate.price}</div>
                {typeof priceDelta !== "undefined" && (
                    <div className="shipping-rate-price-delta">
                        <span className="marker-red dashicons dashicons-warning"></span>
                        {__('Price change:')} {priceDelta > 0 ? "+" : ""}
                        {priceDelta}
                    </div>
                )}
            </div>
            <div className="shipping-rate-estimated-delivery">
                {__('Estimated delivery date :')} {shippingRate.deliveryDate}
            </div>
            <div className="shipping-rate-pickup-date">
                {__('Pickup date :')} {shippingRate.pickupDate}
            </div>
            
            {requiredAttachments.length > 0 && (
                <Attachments
                    attachments={attachments}
                    requiredAttachments={requiredAttachments}
                    remainingAttachments={remainingAttachments}
                />
            )}

            <Packaging products={products} packaging={[]} onChange={() => {}} />
        </div>
    );
};

export default ShippingRowBody;