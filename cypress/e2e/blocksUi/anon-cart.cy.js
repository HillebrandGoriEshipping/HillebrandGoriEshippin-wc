import addToCart from "../../support/addToCart";
import { selectRateInAccordion } from "../../support/shippingRates";

describe('Block UI Cart spec', () => {
  before(() => {
    cy.task('setUiToBlocks');
  });
  
  beforeEach(() => {
    addToCart('blocks');
  });

  it('Select a shipping method in cart view', () => {
    cy.visit('/cart');

    // Define a country and a city to enable the display of the shipping rates
    const cartShippingFormButton = cy.get('.wc-block-components-totals-shipping-panel > div[role="button"]').should('be.visible');
    cartShippingFormButton.click();
    cy.get('.wc-block-components-address-form__postcode input').type('45000');
    cy.get('.wc-block-components-address-form__city input').type('Orléans');
    cy.get('form.wc-block-components-shipping-calculator-address .wc-block-components-shipping-calculator-address__button').click();

    // select a shipping method
    selectRateInAccordion('Door Delivery', 'Chrono 18');
    // then another
    selectRateInAccordion('Other shipping method', 'Flat rate');
    console.log('Anon Cart spec done');
  });

  it('Remove items from cart', () => {
    cy.visit('/cart');
    cy.get('.wc-block-cart-item__remove-link').click({ multiple: true });
    cy.wait(10000);

    // Check that the cart is empty
    cy.get('.wp-block-woocommerce-empty-cart-block').should('exist');
    cy.get('.wp-block-woocommerce-empty-cart-block').should('be.visible');
  });
});