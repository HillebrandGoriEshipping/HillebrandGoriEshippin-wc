import addToCart from "../../support/addToCart";
import { shippingAddressFormFill } from "../../support/formFill";
import { checkOrderConfirmationContent } from "../../support/orderConfirmation";

describe('Classic UI Cart spec', () => {
  before(() => {
    cy.task('setUiToClassic');
  });

  beforeEach(() => {
    addToCart('classic');
  });

  it('Places order with custom address fields', () => {
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

    const isCompanyCheckboxId = '#wc_billing\\/hges\\/is-company-address';
    const companyNameId = '#wc_billing\\/hges\\/company-name';
    cy.get(isCompanyCheckboxId).then(checkbox => {
      cy.wrap(checkbox).should('be.visible');
      cy.wrap(checkbox).should('not.be.checked');
      cy.wrap(checkbox).click();
      cy.wrap(checkbox).should('be.checked');
    });
   

    cy.get(companyNameId).then(companyNameField => {
      cy.wrap(companyNameField).should('be.visible');
      cy.wrap(companyNameField).should('have.value', '');
      cy.wrap(companyNameField).type('Test Company');
      cy.wrap(companyNameField).should('have.value', 'Test Company');
    });
  
    cy.contains('Flat rate').should('be.visible');
    cy.contains('Flat rate').click();
    cy.contains('Flat rate').closest('label').invoke('attr', 'for').then((id) => {
      cy.get(`#${id}`).should('be.checked');
    });

    cy.get('button[name="woocommerce_checkout_place_order"]').should('be.visible');
    cy.get('button[name="woocommerce_checkout_place_order"]').click();
    
    checkOrderConfirmationContent(false);
  });

  it('Saves custom buisiness order address fields', () => {
    cy.visit('/checkout');

    shippingAddressFormFill('classic', {
      'billing_first_name': 'Jean',
      'billing_last_name': 'Némar',
      'billing_address_1': '1 rue du Test Automatisé',
      'billing_postcode': '45000',
      'billing_city': 'Orléans',
      'billing_email': 'test@test.com',
      'wc_billing/hges/is-company-address': true,
      'wc_billing/hges/company-name': 'Test Company',
      'wc_billing/hges/excise-number': '12345678901234'
    });

    cy.get('button[name="woocommerce_checkout_place_order"]').should('be.visible');
    cy.get('button[name="woocommerce_checkout_place_order"]').click();
    
    checkOrderConfirmationContent(true);
  });
});