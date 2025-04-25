const { __ } = window.wp.i18n;
const { registerPlugin } = window.wp.plugins;
const { select } = window.wp.data;
const { ExperimentalOrderShippingPackages } = window.wc.blocksCheckout;
import { useState } from "react";

const Accordion = ({ title, children }) => {
  const [isOpen, setIsOpen] = useState(false);
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

const onClickedRate = (e) => {
  window.dispatchEvent(new Event("change"));
};

const RateGroup = ({ rates, onClickedRate }) => (
  <div>
    {rates.map((rate) => (
      <label htmlFor={"radio-control-0-" + rate.name} key={rate.key}>
        <div
          className={"rate-content" + (rate.selected ? " selected" : "")}
          onClick={() => onClickedRate(rate)}
        >
          <div className="rate-left">
            <div className="rate-logo">
              <img
                src={assetsPath.assetsUrl + rate.carrierName + ".png"}
                alt={rate.name}
                className="rate-logo-image"
              />
            </div>
            <div className="rate-info">
              <p className="rate-name">{rate.name}</p>
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

const MyCustomComponent = (props) => {
  const store = select("wc/store/cart");
  const shippingPackages = store.getShippingRates();
  const shippingRates = shippingPackages[0].shipping_rates.map((r, i) => {
    const newRate = {
      ...r,
      key: i,
    };

    r.meta_data.forEach((md) => {
      r[md.key] = md.value;
    });

    console.log(newRate);
    return newRate;
  });

  // Filter the shipping rates based on the door delivery property
  const doorDeliveryRates = shippingRates.filter(
    (rate) => rate.doorDelivery === "1"
  );
  const otherRates = shippingRates.filter((rate) => rate.doorDelivery === "");

  return (
    <div className="shipping-rates">
      <Accordion title={__("Pickup points", "HillebrandGoriEshipping")}>
        <RateGroup rates={otherRates} onClickedRate={onClickedRate} />
      </Accordion>
      <Accordion title={__("Door Delivery", "HillebrandGoriEshipping")}>
        <RateGroup rates={doorDeliveryRates} onClickedRate={onClickedRate} />
      </Accordion>
    </div>
  );
};

const render = () => {
  return (
    <ExperimentalOrderShippingPackages>
      <MyCustomComponent />
    </ExperimentalOrderShippingPackages>
  );
};
registerPlugin("slot-and-fill-examples", {
  render,
  scope: "woocommerce-checkout",
});
