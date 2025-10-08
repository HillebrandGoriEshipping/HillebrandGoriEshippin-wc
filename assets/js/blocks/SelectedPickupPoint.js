import { useState, useRef, useEffect } from "@wordpress/element";
const { translate } = window.hges.i18n;

const SelectedPickupPoint = () => {

    const selectedPickupPointTemplate = useRef(null);
    const [selectedPickupPoint, setSelectedPickupPoint] = useState(null);

    window.addEventListener('hges:pickup-points-selected', (e) => {
        setSelectedPickupPoint(e.detail.pickupPoint);
    });

    useEffect(() => {
        const totalsShippingContainer = document.querySelector('.wc-block-components-totals-shipping');
        if (! totalsShippingContainer) {
            return;
        }
        
        const formerSelectedBlock = totalsShippingContainer.querySelector('#totals-selected-pickup-point-current');
        if (formerSelectedBlock) {
            totalsShippingContainer.removeChild(formerSelectedBlock);
        }
        
        if (!selectedPickupPoint) {
            return;
        }

        const newSelectedPickupPointBlock = selectedPickupPointTemplate.current.cloneNode(true);
        newSelectedPickupPointBlock.id = `totals-selected-pickup-point-current`;
        newSelectedPickupPointBlock.classList.remove("hidden");
        totalsShippingContainer.appendChild(newSelectedPickupPointBlock);
        const button = newSelectedPickupPointBlock.querySelector('.button');
        if (button) {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                window.dispatchEvent(new Event('hges:show-pickup-points-map'));
            });
        }
    }, [selectedPickupPoint]);

    return (
        <div id="totals-selected-pickup-point-template" ref={selectedPickupPointTemplate}>
            { selectedPickupPoint && (
                <div className="selected-pickup-point">
                    <h3>{ selectedPickupPoint.name }</h3>
                    <p>{ selectedPickupPoint.addLine1 }</p>
                    <p>{ selectedPickupPoint.zipCode } { selectedPickupPoint.city }</p>
                    <div className="button-container">
                        <button className="button">{ translate('Choose another pickup point') }</button>
                    </div>
                </div>
            ) }
        </div>
    )
}

export default SelectedPickupPoint;