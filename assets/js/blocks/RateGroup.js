const { __ } = window.wp.i18n;
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
  const packageIndex = radioInput.id.split("-")[2];

  return `radio-control-${packageIndex}-${rate.rate_id}`;
};
// hack, waiting for WooCommerce to build a customizable shuipping method block
//wc-blocks_render_blocks_frontend
const RateGroup = ({ rates, hasLogo = true }) => {
  rates = rates.map((rate) => {
    let logoUrl = assetsPath.assetsUrl + rate.carrierName + ".png";

    if (rate.name == "Aérien") {
      logoUrl = assetsPath.assetsUrl + "airfreight.png";
    } else if (rate.name === "Maritime") {
      logoUrl = assetsPath.assetsUrl + "seafreight.png";
    }
    return {
      ...rate,
      logoUrl: logoUrl,
    };
  });

  return (
    <div>
      {rates.map((rate) => (
        <label htmlFor={getRadioButtonId(rate)} key={rate.key}>
          <div className={"rate-content" + (rate.selected ? " selected" : "")}>
            <div className="rate-left">
              {hasLogo ? (
                <div className="rate-logo">
                  <img
                    src={rate.logoUrl}
                    alt={rate.name}
                    className="rate-logo-image"
                  />
                </div>
              ) : (
                ""
              )}
              <div className="rate-info">
                <p className="rate-name">
                  {hasLogo ? (
                    <img
                      src={rate.logoUrl}
                      alt={rate.name}
                      className="rate-logo-image"
                    />
                  ) : (
                    ""
                  )}
                  <span>{rate.name}</span>
                </p>
                <div className="rate-date-box">
                  <p>{__("Estimated delivery: ", "HillebrandGoriEshipping")}</p>
                  <p className="rate-estimated-date">
                    {dayjs(rate.pickupDate).format("LL")}
                  </p>
                </div>
              </div>
            </div>
            <div className="rate-right">
              <p className="rate-price">
                {rate.currency_prefix}
                {Number(rate.price / 100).toFixed(2)}
                {rate.currency_suffix}
              </p>
            </div>
          </div>
        </label>
      ))}
    </div>
  );
};

export default RateGroup;
