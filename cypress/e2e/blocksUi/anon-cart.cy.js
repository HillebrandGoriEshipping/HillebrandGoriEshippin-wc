import addToCart from "../../support/addToCart";
import { selectRateInAccordion } from "../../support/shippingRates";
import { blocksFillDeliveryAddress } from "../../support/cartPageHelper";

describe('Block UI Cart spec', () => {
  before(() => {
    cy.task('setUiToBlocks');
  });
  
  beforeEach(() => {
    addToCart('blocks');
  });

  it('Select a shipping method in cart view', () => {
    cy.visit('/cart');
    cy.get('h1').contains('Cart').should('be.visible');
    blocksFillDeliveryAddress();

    // select a shipping method
    selectRateInAccordion('Door Delivery', 0);
    selectRateInAccordion('Other shipping method', 'Flat rate');
    // then another
    console.log('Anon Cart spec done');
  });

  it('Select a pickup delivery mode shipping method in checkout view', () => {
    cy.visit('/cart');
    cy.get('h1').contains('Cart').should('be.visible');
    blocksFillDeliveryAddress();

    // select a pickup shipping method
    selectRateInAccordion('Pickup points', 0);

    cy.get('.rate-content.selected').then(($label) => {
      const pickupButton = cy.wrap($label).get('div.pickup-point-button > button').should('not.exist');
    });
  });

  it('Remove items from cart', () => {
    cy.visit('/cart');
    cy.get('h1').contains('Cart').should('be.visible');
    cy.get('.wc-block-cart-item__remove-link').click({ multiple: true });
    cy.wait(10000);

    // Check that the cart is empty
    cy.get('.wp-block-woocommerce-empty-cart-block').should('exist');
    cy.get('.wp-block-woocommerce-empty-cart-block').should('be.visible');
  });
});