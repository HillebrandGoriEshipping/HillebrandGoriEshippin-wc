import addToCart from "../../support/addToCart";
import { selectRateInAccordion } from "../../support/shippingRates";

describe('Classic UI Cart spec', () => {
  before(() => {
    cy.task('setUiToClassic');
  });

  beforeEach(() => {
    addToCart('classic');
  });

  it('Select a shipping method in cart view', () => {
    cy.visit('/cart');

    // Define a country and a city to enable the display of the shipping rates
    const cartCalculateShippingLink = cy.get('a').contains('Calculate shipping').should('be.visible');
    cartCalculateShippingLink.click();

    cy.get('.shipping-calculator-form #calc_shipping_city').type('Orléans');
    cy.get('.shipping-calculator-form #calc_shipping_postcode').type('45000');
    cy.get('.shipping-calculator-form button').contains('Update').click();
  });
});