const { __ } = window.wp.i18n;

import RateGroup from "./RateGroup";
import Accordion from "./Accordion";

const ShippingRatesContainer = ({
  doorDeliveryRates,
  pickupRates,
  setLoading,
}) => {
  if (doorDeliveryRates.length === 0) {
    return (
      <div className="shipping-rates">
        <RateGroup rates={pickupRates} setLoading={setLoading} />
      </div>
    );
  } else if (pickupRates.length === 0) {
    return (
      <div className="shipping-rates">
        <RateGroup rates={doorDeliveryRates} setLoading={setLoading} />
      </div>
    );
  } else {
    return (
      <div className="shipping-rates">
        <Accordion
          title={__("Pickup points", "HillebrandGoriEshipping")}
          defaultOpen={!!pickupRates.find((r) => r.selected)}
        >
          <RateGroup rates={pickupRates} setLoading={setLoading} />
        </Accordion>
        <Accordion
          title={__("Door Delivery", "HillebrandGoriEshipping")}
          defaultOpen={!!doorDeliveryRates.find((r) => r.selected)}
        >
          <RateGroup rates={doorDeliveryRates} setLoading={setLoading} />
        </Accordion>
      </div>
    );
  }
};

export default ShippingRatesContainer;
