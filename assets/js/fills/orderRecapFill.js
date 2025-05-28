const { registerPlugin } = window.wp.plugins;
const { ExperimentalOrderMeta } = window.wc.blocksCheckout;
import LoadingMask from "../blocks/LoadingMask";
import SelectedPickupPoint from "../blocks/SelectedPickupPoint";

const render = () => {
  return (
    <ExperimentalOrderMeta>
      <div className="order-totals-shipping-rates-hges-fill">
        <LoadingMask
          className="order-totals-shipping-rates-loading-mask"
          isLoading={true}
          showSpinner={true}
        />
        <SelectedPickupPoint />
      </div>
    </ExperimentalOrderMeta>
  );
};
registerPlugin("hges-order-recap-fill", {
  render,
  scope: "woocommerce-checkout",
});
