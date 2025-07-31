const __ = wp.i18n.__;

const ShippingRowBody = ({
    shippingRate,
    attachments = [],
    remainingAttachments = [],
    itemId,
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
                <div>
                    <h3>{__('Required attachments')}</h3>
                    <div>
                        <p>
                            {attachments.length}/{requiredAttachments.length}
                        </p>
                        <ul className="attchments-preview">
                            {attachments.map((attachment) => (
                                <li key={attachment.type}>
                                    <span
                                        className="marker-green dashicons dashicons-yes-alt"
                                        id={`attachments-marker-${attachment.type}`}
                                    ></span>
                                    {attachment.label}
                                    <input
                                        type="file"
                                        data-file-label={attachment.label}
                                        data-file-type={attachment.type}
                                        name="fileUpload"
                                        className="filepond-file-input"
                                        id={`attachments-input-${attachment.type}`}
                                    />
                                </li>
                            ))}
                            {remainingAttachments.map((remainingAttachment) => (
                                <li key={remainingAttachment.type}>
                                    <span
                                        className="marker-red dashicons dashicons-marker"
                                        id={`attachments-marker-${remainingAttachment.type}`}
                                    ></span>
                                    {remainingAttachment.label}
                                    <input
                                        type="file"
                                        data-file-label={remainingAttachment.label}
                                        data-file-type={remainingAttachment.type}
                                        name="fileUpload"
                                        className="filepond-file-input"
                                        id={`attachments-input-${remainingAttachment.type}`}
                                    />
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            )}
        </div>
    );
};