import clsx from "clsx";

export default ({ rate, onSelect, isSelected }) => {
    const { meta_data, checksum, label, cost, carrier } = rate;
    let logoUrl = window.hges.assetsUrl + "img/" + carrier + ".png";

    if (rate.service == "Aérien") {
        logoUrl = window.hges.assetsUrl + "img/airfreight.png";
    } else if (rate.service === "Maritime") {
        logoUrl = window.hges.assetsUrl + "img/seafreight.png";
    }
    const openMapModal = (e) => {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('hges:show-pickup-points-map', { detail: { rate } }));
    };
        return (
            <div
                id={`shipping-rate-${checksum}`}
                onClick={() => onSelect(checksum)}
                className={clsx("hges-shipping-method", { selected: isSelected })}
            >
                <div className="hges-shipping-method-left">
                    {carrier != null && (
                        <div className="hges-shipping-logo">
                            <img src={logoUrl} alt="Hillebrand Gori Eshipping" />
                        </div>
                    )}
                    <div className="hges-shipping-info">
                        <div className="hges-shipping-label">
                            {label}
                        </div>
                        {meta_data.deliveryDate && (
                            <div className="hges-shipping-delivery">
                                Estimated delivery: {meta_data.deliveryDate}
                            </div>
                        )}
                        {meta_data.deliveryDate && meta_data.deliveryMode === "pickup" && (
                            <div
                                id="pickup-point-button"
                                className="pickup-point-button"
                                onClick={e => {
                                    e.stopPropagation();
                                    openMapModal(e);
                                }}
                            >
                                <span>Choose your pickup point</span>
                            </div>
                        )}
                    </div>
                    {meta_data.deliveryMode === "pickup" && (
                        <div id="selected-pickup-point" className="selected-pickup-point hidden">
                            <strong>Selected pickup point:</strong>
                            <span id="selected-pickup-point-name"></span>
                            <span id="selected-pickup-point-address"></span>
                        </div>
                    )}
                </div>
                <div className="hges-shipping-price">
                    {cost}
                </div>
            </div>
        );
    };
