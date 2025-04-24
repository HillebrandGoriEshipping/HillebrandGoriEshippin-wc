const { __ } = window.wp.i18n;
const { registerPlugin } = window.wp.plugins;
const { select } = window.wp.data;
const { ExperimentalOrderShippingPackages } = window.wc.blocksCheckout;

// hack, waiting for WooCommerce to build a customizable shuipping method block
//wc-blocks_render_blocks_frontend

const onClickedRate = (e) => {
  console.log(e);
  window.dispatchEvent(new Event("change"));
};

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

  return (
    <div className="shipping-rates">
      {shippingRates.map((rate) => (
        <label for={"radio-control-0-" + rate.name}>
          <div
            className={"rate-content" + (rate.selected ? " selected" : "")}
            key={rate.key}
            onClick={(e) => onClickedRate(rate)}
          >
            <div className="rate-left">
              <div className="rate-logo">
                <img
                  src={rate.logo}
                  alt={rate.name}
                  className="rate-logo-image"
                />
              </div>
              <div className="rate-info">
                <p className="rate-name">{rate.name}</p>

                <p className="rate-estimated-date">
                  {__("Estimated delivery: ", "HillebrandGoriEshipping")}
                  {rate.eta}
                </p>
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
