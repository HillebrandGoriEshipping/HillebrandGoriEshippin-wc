import addToCart from "../../support/addToCart";
import { selectRateInAccordion } from "../../support/shippingRates";
import { shippingAddressFormFill } from "../../support/formFill";

describe('Checkout spec', () => {
  before(() => {
    cy.task('setUiToBlocks');
  });
  
  beforeEach(() => {
    addToCart();
  });

  it('Select a shipping method in checkout view', () => {
    cy.visit('/cart');

    cy.contains('Proceed to Checkout').click();

    shippingAddressFormFill({
      'shipping-first_name': 'Jean',
      'shipping-last_name': 'Némar',
      'shipping-address_1': '1 rue du Test Automatisé',
      'shipping-postcode': '45000',
      'shipping-city': 'Orléans'
    });

    // select a shipping method
    selectRateInAccordion('Other shipping method', 'Flat rate');
    // then another
    selectRateInAccordion('Door Delivery', 'Chrono 18');
  });
});