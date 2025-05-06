const { __ } = window.wp.i18n;

import RateGroup from "./RateGroup";
import Accordion from "./Accordion";
import PickupPointsMap from "./PickupPointsMap";

const ShippingRatesContainer = ({
  doorDeliveryRates,
  pickupRates,
  otherRates,
  setLoading,
}) => {
  return (
    <div className="shipping-rates">
      <Accordion
        title={__("Pickup points", "hges")}
        defaultOpen={!!pickupRates.find((r) => r.selected)}
        display={pickupRates.length > 0}
        displayHeader={doorDeliveryRates.length > 0 || otherRates.length > 0}
      >
        <RateGroup rates={pickupRates} setLoading={setLoading} />
      </Accordion>
      <Accordion
        title={__("Door Delivery", "hges")}
        defaultOpen={!!doorDeliveryRates.find((r) => r.selected)}
        display={doorDeliveryRates.length > 0}
        displayHeader={pickupRates.length > 0 || otherRates.length > 0}
      >
        <RateGroup rates={doorDeliveryRates} setLoading={setLoading} />
      </Accordion>
      <Accordion
        title={__("Other shipping method", "hges")}
        defaultOpen={!!otherRates.find((r) => r.selected)}
        display={otherRates.length > 0}
        displayHeader={doorDeliveryRates.length > 0 || pickupRates.length > 0}
      >
        <RateGroup rates={otherRates} setLoading={setLoading} hasLogo={false} />
      </Accordion>

      { pickupRates.length > 0 && (
        <PickupPointsMap />
      )}
    </div>
  );
};

export default ShippingRatesContainer;
