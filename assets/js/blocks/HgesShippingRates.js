import { useState, useEffect } from "react";
import { useSelect } from "@wordpress/data";
const { select } = window.wp.data;
const { __ } = window.wp.i18n;

import LoadingMask from "../blocks/LoadingMask";
import ShippingRatesContainer from "../blocks/ShippingRatesContainer";
const cartStore = wp.data.select("wc/store/cart");

const HgesShippingRates = (data) => {
  const [loading, setLoading] = useState(false);

  const shippingPackages = useSelect(
    () => cartStore.getShippingRates(),
    []
  );

 useEffect(() => {
  const unsubscribe = wp.data.subscribe(() => {

    const isRateBeingSelected = cartStore?.isShippingRateBeingSelected?.() || false;
    setLoading(isRateBeingSelected);

    const loadingMask = document.querySelector(".order-totals-shipping-rates-loading-mask");
    const totalsShippingLine = document.querySelector(".wc-block-components-totals-shipping");

    // We check if the loading mask and totalsShippingLine are available, if not we return
    if (!loadingMask || !totalsShippingLine || !totalsShippingLine.parentElement) {
      return;
    }

    // We check if the loading mask is already appended to avoid duplicates
    const alreadyAppended = Array.from(totalsShippingLine.parentElement.children).includes(loadingMask);

    if (!alreadyAppended) {
      // We clone the loading mask to avoid conflicts with other plugins
      const clonedMask = loadingMask.cloneNode(true);
      clonedMask.style.display = isRateBeingSelected ? "block" : "none";
      clonedMask.classList.add("cloned-shipping-mask");
      totalsShippingLine.parentElement.appendChild(clonedMask);
    } else {
      const clonedMask = document.querySelector(".cloned-shipping-mask");
      if (clonedMask) {
        clonedMask.style.display = isRateBeingSelected ? "block" : "none";
      }
    }

    totalsShippingLine.style.display = isRateBeingSelected ? "none" : "block";
  });

  return () => unsubscribe?.();
}, []);


  const currentContext = data.context;
  if (
    !Array.isArray(shippingPackages) ||
    shippingPackages.length === 0 ||
    !shippingPackages[0]?.shipping_rates ||
    shippingPackages[0].shipping_rates.length === 0
  ) {
    return null;
  }

  const rates = shippingPackages[0].shipping_rates;

  const shippingRates = [];

  rates.forEach((r, i) => {
    if (!r || r.method_id === "pickup_location") return;

    r.meta_data?.forEach((md) => {
      if (md?.key) {
        r[md.key] = md.value;
      }
    });

    shippingRates.push({
      ...r,
      key: i,
    });
  });

  const doorDeliveryRates = [];
  const pickupRates = [];
  const otherRates = [];

  shippingRates.forEach((rate) => {
    if (rate.doorDelivery === "1") {
      rate.isPickup = false;
      doorDeliveryRates.push(rate);
    } else if (rate.doorDelivery === "") {
      rate.isPickup = true;
      pickupRates.push(rate);
    } else {
      rate.isPickup = false;
      otherRates.push(rate);
    }
  });

  return (
    <LoadingMask
      isLoading={loading}
      screenReaderLabel={__("Loading shipping rates", "hges")}
      showSpinner={true}
    >
      <ShippingRatesContainer
        doorDeliveryRates={doorDeliveryRates}
        pickupRates={pickupRates}
        otherRates={otherRates}
        setLoading={setLoading}
        currentContext={currentContext}
      />
    </LoadingMask>
  );
};

export default HgesShippingRates;
