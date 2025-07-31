const __ = wp.i18n.__;
import apiClient from '../../../../../apiClient';
import ShippingRateModal from './ShippingRateModal';
import ShippingRowBody from './ShippingRowBody';

const ShippingMethodRow = ({
    errorMessage,
    stillAvailable,
    shippingRate,
    attachments = [],
    remainingAttachments = [],
    itemId,
}) => {
    // set isRateSelectionModalOpen as state variable
    const [isRateSelectionModalOpen, setIsRateSelectionModalOpen] = React.useState(false);
    const requiredAttachments = shippingRate?.requiredAttachments || [];
    const priceDelta = shippingRate?.meta_data?.priceDelta;
    const openShippingRateModal = async (e) =>{
        e.preventDefault();
        setIsRateSelectionModalOpen(true);
    }

    const closeShippingRateModal = () =>{
        setIsRateSelectionModalOpen(false);
    }

    const onShippingRateSelected = (event) => {
        document.querySelectorAll('#hges-shipping-rate-modal .shipping-rate-list .hges-shipping-method').forEach((rateElement) => {
            rateElement.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        this.selectedShippingRateChecksum = event.currentTarget.dataset.checksum;
    }
    const updateShippingRate = async () => {
        if (this.selectedShippingRateChecksum) {
            this.selectedRate = await apiClient.post(
                window.hges.ajaxUrl, 
                {
                    action: 'hges_set_order_shipping_rate',
                    orderId: new URLSearchParams(window.location.search).get('id'),
                    orderShippingItemId: this.currentEditingItemId,
                }, 
                {
                    shippingRateChecksum: this.selectedShippingRateChecksum
                },
            );
            
            window.location.reload();
        }
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
                    onShippingRateSelected={onShippingRateSelected}
                />
            </div>
        );
    };
    return render();
};

export default ShippingMethodRow;
