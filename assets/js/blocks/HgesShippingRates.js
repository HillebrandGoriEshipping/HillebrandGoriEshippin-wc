const { __ } = window.wp.i18n;
const { select } = window.wp.data;
import { useState } from "react";

import LoadingMask from "../blocks/LoadingMask";
import ShippingRatesContainer from "../blocks/ShippingRatesContainer";

const HgesShippingRates = () => {
  const [loading, setLoading] = useState(false);
  const cartStore = select("wc/store/cart");
  const shippingPackages = cartStore.getShippingRates();

  wp.data.subscribe(() => {
    const isRateBeingSelected = cartStore.isShippingRateBeingSelected();
    setLoading(isRateBeingSelected);

    const LoadingMask = document.querySelector(
      ".order-totals-shipping-rates-loading-mask"
    );
    const totalsShippingLine = document.querySelector(
      ".wc-block-components-totals-shipping"
    );
    if (totalsShippingLine) {
      totalsShippingLine.parentElement.appendChild(LoadingMask);
    }

    if (isRateBeingSelected) {
      totalsShippingLine.style.display = "none";
      LoadingMask.style.display = "block";
    } else {
      totalsShippingLine.style.display = "block";
      LoadingMask.style.display = "none";
    }
  });

  const shippingRates = [];

  shippingPackages[0].shipping_rates.forEach((r, i) => {
    if (r.method_id === "pickup_location") {
      return;
    }
    const newRate = {
      ...r,
      key: i,
    };

    r.meta_data.forEach((md) => {
      r[md.key] = md.value;
    });

    shippingRates.push(newRate);
  });

  // Sort the shipping rates by the door delivery property
  const doorDeliveryRates = [];
  const pickupRates = [];
  const otherRates = [];
  shippingRates.forEach((rate) => {
    if (rate.doorDelivery === "1") {
      doorDeliveryRates.push(rate);
    } else if (rate.doorDelivery === "") {
      pickupRates.push(rate);
    } else {
      otherRates.push(rate);
    }
  });

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
        otherRates={otherRates}
        setLoading={setLoading}
      />
    </LoadingMask>
  );
};

export default HgesShippingRates;
