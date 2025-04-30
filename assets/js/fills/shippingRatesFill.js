const { registerPlugin } = window.wp.plugins;

const { ExperimentalOrderShippingPackages } = window.wc.blocksCheckout;

import HgesShippingRates from "../blocks/HgesShippingRates";

const render = () => {
  return (
    <ExperimentalOrderShippingPackages>
      <HgesShippingRates />
    </ExperimentalOrderShippingPackages>
  );
};
registerPlugin("hges-shipping-rates-fill", {
  render,
  scope: "woocommerce-checkout",
});
