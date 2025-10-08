const { translate } = window.hges.i18n;

import RateGroup from "./RateGroup";
import Accordion from "./Accordion";
import PickupPointsMap from "./PickupPointsMap";

const ShippingRatesContainer = ({
  doorDeliveryRates,
  pickupRates,
  otherRates,
  setLoading,
  currentContext,
}) => {
  return (
    <div className="shipping-rates">
      <Accordion
        title={ translate("Pickup points") }
        defaultOpen={!!pickupRates.find((r) => r.selected) }
        display={pickupRates.length > 0}
        displayHeader={doorDeliveryRates.length > 0 || otherRates.length > 0}
      >
        <RateGroup rates={pickupRates} setLoading={setLoading} currentContext={currentContext}/>
      </Accordion>
      <Accordion
        title={ translate("Door Delivery") }
        defaultOpen={!!doorDeliveryRates.find((r) => r.selected) }
        display={doorDeliveryRates.length > 0}
        displayHeader={pickupRates.length > 0 || otherRates.length > 0}
      >
        <RateGroup rates={doorDeliveryRates} setLoading={setLoading} currentContext={currentContext}/>
      </Accordion>
      <Accordion
        title={ translate("Other shipping method") }
        defaultOpen={!!otherRates.find((r) => r.selected) }
        display={otherRates.length > 0}
        displayHeader={doorDeliveryRates.length > 0 || pickupRates.length > 0}
      >
        <RateGroup rates={otherRates} setLoading={setLoading} hasLogo={false} currentContext={currentContext}/>
      </Accordion>

      { pickupRates.length > 0 && (
        <PickupPointsMap />
      ) }
    </div>
  );
};

export default ShippingRatesContainer;
