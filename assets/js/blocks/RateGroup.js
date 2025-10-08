const { translate } = window.hges.i18n;
const { select } = window.wp.data;

// Date format localization
import dayjs from "dayjs";
import localizedFormat from "dayjs/plugin/localizedFormat";
import "dayjs/locale/fr";
import "dayjs/locale/en";
import "dayjs/locale/de";
dayjs.extend(localizedFormat);

const browserLang = navigator.language || "en";
const langCode = browserLang.split("-")[0];
dayjs.locale(langCode);

const getRadioButtonId = (rate) => {
  const radioInput = document.querySelector(
    ".wc-block-components-shipping-rates-control__package input[type='radio']"
  );
  let packageIndex = 0;
  if (radioInput) {
    packageIndex = radioInput.id.split("-")[2];
  }

  return `radio-control-${packageIndex}-${rate.rate_id}`;
};
// hack, waiting for WooCommerce to build a customizable shuipping method block
//wc-blocks_render_blocks_frontend
const RateGroup = ({ rates, currentContext, hasLogo = true }) => {
  rates = rates.map((rate) => {
    let logoUrl = window.hges.assetsUrl + "img/" + rate.carrier + ".png";
    
    if (rate.service == "Aérien") {
      logoUrl = window.hges.assetsUrl + "img/airfreight.png";
    } else if (rate.service === "Maritime") {
      logoUrl = window.hges.assetsUrl + "img/seafreight.png";
    }
    return {
      ...rate,
      logoUrl: logoUrl,
    };
  });
  
  const openSelectPickupPointModal = (e) => {
    e.preventDefault();
    const label = e.currentTarget.closest("label");
    label.dispatchEvent(new Event("click"));
    const rate = rates.find(r => r.checksum === label.getAttribute("rate"));
    window.dispatchEvent(new CustomEvent('hges:show-pickup-points-map', { detail: { rate } }));
  }

  return (
    <div>
      {rates.map((rate) => (
        <label rate={rate.checksum} htmlFor={getRadioButtonId(rate) } key={rate.key}>
          <div className={"rate-content" + (rate.selected ? " selected" : "") }>
            <div className="rate-left">
              {hasLogo ? (
                <div className="rate-logo">
                  <img
                    src={rate.logoUrl}
                    alt={rate.carrier}
                    className="rate-logo-image"
                  />
                </div>
              ) : (
                ""
              ) }
              <div className="rate-info">
                <p className="rate-name">
                  {hasLogo ? (
                    <img
                      src={rate.logoUrl}
                      alt={rate.carrier}
                      className="rate-logo-image"
                    />
                  ) : (
                    ""
                  ) }
                  <span>{rate.name}</span>
                            
                </p>
                
                <div className="rate-date-box">
                  <p className="rate-estimated-date">
                    { translate("Estimated delivery:") } {dayjs(rate.deliveryDate).format("LL") }
                  </p>
                  {!rate.isPickup || currentContext === 'woocommerce/cart' ? '' : (
                    <p className="rate-price-pickup">
                      {rate.currency_prefix}
                      {Number(rate.price / 100).toFixed(2) }
                      {rate.currency_suffix}
                    </p>
                  ) }
                </div>
              </div>
              </div>
              <div className="rate-right">
              {!rate.isPickup || !rate.selected || currentContext === 'woocommerce/cart' ? '': (
                <div className="pickup-point-button-block">
                  <button onClick={openSelectPickupPointModal}>{ translate(`Choose your pickup point`) }</button>
                </div>
              ) }   
              {rate.isPickup || currentContext === 'woocommerce/cart' ? '' : (
              <p className="rate-price">
                {rate.currency_prefix}
                {Number(rate.price / 100).toFixed(2) }
                {rate.currency_suffix}
              </p>
            ) }
            </div>
          </div>
        </label>
      )) }
    </div>
  );
};

export default RateGroup;
