const { registerPlugin } = window.wp.plugins;
const { ExperimentalOrderMeta } = window.wc.blocksCheckout;
import LoadingMask from "../blocks/LoadingMask";

const render = () => {
  return (
    <ExperimentalOrderMeta>
      <LoadingMask
        className="order-totals-shipping-rates-loading-mask"
        isLoading={true}
        showSpinner={true}
      />
    </ExperimentalOrderMeta>
  );
};
registerPlugin("hges-order-recap-fill", {
  render,
  scope: "woocommerce-checkout",
});
