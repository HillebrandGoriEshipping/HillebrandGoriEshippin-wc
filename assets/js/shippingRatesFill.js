const { __ } = window.wp.i18n;
const { registerPlugin } = window.wp.plugins;
const { select } = window.wp.data;
const { ExperimentalOrderShippingPackages } = window.wc.blocksCheckout;
import { useState } from "react";
import clsx from "clsx";
const { Spinner } = window.wc.blocksComponents;

const LoadingMask = ({
  children,
  className,
  screenReaderLabel,
  showSpinner = false,
  isLoading = true,
}) => {
  return (
    <div
      className={clsx(className, {
        "wc-block-components-loading-mask": isLoading,
      })}
    >
      {isLoading && showSpinner && <Spinner />}
      <div
        className={clsx({
          "wc-block-components-loading-mask__children": isLoading,
        })}
        aria-hidden={isLoading}
      >
        {children}
      </div>
      {isLoading && (
        <span className="screen-reader-text">
          {screenReaderLabel || __("Loading…", "woocommerce")}
        </span>
      )}
    </div>
  );
};

const Accordion = ({ title, children, defaultOpen }) => {
  const [isOpen, setIsOpen] = useState(defaultOpen || false);

  return (
    <div className="accordion">
      <button
        className="accordion-header"
        onClick={(e) => {
          e.preventDefault();
          setIsOpen(!isOpen);
        }}
        aria-expanded={isOpen}
      >
        <span>{isOpen ? "− " : "+ "}</span>
        <span>{title}</span>
      </button>
      {isOpen && <div className="accordion-content">{children}</div>}
    </div>
  );
};

// hack, waiting for WooCommerce to build a customizable shuipping method block
//wc-blocks_render_blocks_frontend
const RateGroup = ({ rates }) => {
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
        <label htmlFor={"radio-control-0-" + rate.name} key={rate.key}>
          <div className={"rate-content" + (rate.selected ? " selected" : "")}>
            <div className="rate-left">
              <div className="rate-logo">
                <img
                  src={rate.logoUrl}
                  alt={rate.name}
                  className="rate-logo-image"
                />
              </div>
              <div className="rate-info">
                <p className="rate-name">
                  <img
                    src={rate.logoUrl}
                    alt={rate.name}
                    className="rate-logo-image"
                  />
                  <span>{rate.name}</span>
                </p>
                <div className="rate-date-box">
                  <p>{__("Estimated delivery: ", "HillebrandGoriEshipping")}</p>
                  <p className="rate-estimated-date">{rate.pickupDate}</p>
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

const ShippingRatesContainer = ({
  doorDeliveryRates,
  pickupRates,
  setLoading,
}) => {
  if (doorDeliveryRates.length === 0) {
    return (
      <div className="shipping-rates">
        <RateGroup rates={pickupRates} setLoading={setLoading} />
      </div>
    );
  } else if (pickupRates.length === 0) {
    return (
      <div className="shipping-rates">
        <RateGroup rates={doorDeliveryRates} setLoading={setLoading} />
      </div>
    );
  } else {
    return (
      <div className="shipping-rates">
        <Accordion
          title={__("Pickup points", "HillebrandGoriEshipping")}
          defaultOpen={!!pickupRates.find((r) => r.selected)}
        >
          <RateGroup rates={pickupRates} setLoading={setLoading} />
        </Accordion>
        <Accordion
          title={__("Door Delivery", "HillebrandGoriEshipping")}
          defaultOpen={!!doorDeliveryRates.find((r) => r.selected)}
        >
          <RateGroup rates={doorDeliveryRates} setLoading={setLoading} />
        </Accordion>
      </div>
    );
  }
};

const HgesShippingRates = () => {
  const [loading, setLoading] = useState(false);
  const cartStore = select("wc/store/cart");
  const shippingPackages = cartStore.getShippingRates();

  wp.data.subscribe(() => {
    const isRateBeingSelected = cartStore.isShippingRateBeingSelected();
    setLoading(isRateBeingSelected);
  });

  const shippingRates = shippingPackages[0].shipping_rates.map((r, i) => {
    const newRate = {
      ...r,
      key: i,
    };

    r.meta_data.forEach((md) => {
      r[md.key] = md.value;
    });

    return newRate;
  });

  // Filter the shipping rates based on the door delivery property
  const doorDeliveryRates = shippingRates.filter(
    (rate) => rate.doorDelivery === "1"
  );
  const pickupRates = shippingRates.filter((rate) => rate.doorDelivery === "");

  return (
    <LoadingMask
      isLoading={loading}
      screenReaderLabel={__(
        "Loading shipping rates",
        "HillebrandGoriEshipping"
      )}
      showSpinner={true}
    >
      <ShippingRatesContainer
        doorDeliveryRates={doorDeliveryRates}
        pickupRates={pickupRates}
        setLoading={setLoading}
      />
    </LoadingMask>
  );
};

const render = () => {
  return (
    <ExperimentalOrderShippingPackages>
      <HgesShippingRates />
    </ExperimentalOrderShippingPackages>
  );
};
registerPlugin("slot-and-fill-examples", {
  render,
  scope: "woocommerce-checkout",
});
