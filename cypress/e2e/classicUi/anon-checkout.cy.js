import addToCart from "../../support/addToCart";
import { shippingAddressFormFill } from "../../support/formFill";

describe('Classic UI Cart spec', () => {
  before(() => {
    cy.task('setUiToClassic');
  });

  beforeEach(() => {
    addToCart('classic');
  });

  it('Go to checkout from cart and select rate', () => {
    cy.visit('/cart');
    cy.contains('Proceed to checkout').should('be.visible').click();

    cy.get('h1').should('have.text', 'Checkout');
    shippingAddressFormFill('classic', {
      'billing_first_name': 'Jean',
      'billing_last_name': 'Némar',
      'billing_address_1': '1 rue du Test Automatisé',
      'billing_postcode': '45000',
      'billing_city': 'Orléans',
      'billing_email': 'test@test.com'
    });

    
    cy.contains('DHL DOMESTIC EXPRESS').should('be.visible');
    cy.contains('DHL DOMESTIC EXPRESS').click();
    cy.contains('DHL DOMESTIC EXPRESS').closest('label').invoke('attr', 'for').then((id) => {
      cy.get(`#${id}`).should('be.checked');
    });

    cy.contains('Flat rate').should('be.visible');
    cy.contains('Flat rate').click();
    cy.contains('Flat rate').closest('label').invoke('attr', 'for').then((id) => {
      cy.get(`#${id}`).should('be.checked');
    });

    cy.contains('TNT 12:00 Express à domicile').should('be.visible');
    cy.contains('TNT 12:00 Express à domicile').click();
    cy.contains('TNT 12:00 Express à domicile').closest('label').invoke('attr', 'for').then((id) => {
      cy.get(`#${id}`).should('be.checked');
    });
  });
});