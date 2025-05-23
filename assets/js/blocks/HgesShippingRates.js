import { useState, useEffect } from "react";
import { useSelect } from "@wordpress/data";
const { __ } = window.wp.i18n;

import LoadingMask from "../blocks/LoadingMask";
import ShippingRatesContainer from "../blocks/ShippingRatesContainer";

const HgesShippingRates = () => {
  const [loading, setLoading] = useState(false);

  const shippingPackages = useSelect(
    (select) => select("wc/store/cart").getShippingRates(),
    []
  );

 useEffect(() => {
  const unsubscribe = wp.data.subscribe(() => {
    const cartStore = wp.data.select("wc/store/cart");

    const isRateBeingSelected = cartStore?.isShippingRateBeingSelected?.() || false;
    setLoading(isRateBeingSelected);

    const loadingMask = document.querySelector(".order-totals-shipping-rates-loading-mask");
    const totalsShippingLine = document.querySelector(".wc-block-components-totals-shipping");

    // On ne fait rien si les éléments n'existent pas encore
    if (!loadingMask || !totalsShippingLine || !totalsShippingLine.parentElement) {
      return;
    }

    // On vérifie que le masque n'est pas déjà dans le parent pour éviter les conflits
    const alreadyAppended = Array.from(totalsShippingLine.parentElement.children).includes(loadingMask);

    if (!alreadyAppended) {
      // On clone le masque au lieu de déplacer l’élément géré par React
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
      if (md?.key) r[md.key] = md.value;
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
      screenReaderLabel={__("Loading shipping rates", "hges")}
      showSpinner={true}
    >
      <ShippingRatesContainer
        doorDeliveryRates={doorDeliveryRates}
        pickupRates={pickupRates}
        otherRates={otherRates}
      />
    </LoadingMask>
  );
};

export default HgesShippingRates;
