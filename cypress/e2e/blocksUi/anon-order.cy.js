import addToCart from "../../support/addToCart";
import { selectRateInAccordion } from "../../support/shippingRates";
import { shippingAddressFormBlocksFill } from "../../support/formFill";
import { checkOrderConfirmationContent } from "../../support/orderConfirmation";

describe('Block UI Order spec', () => {
  before(() => {
    cy.task('setUiToBlocks');
  });
  
  beforeEach(() => {
    addToCart('blocks');
  });

  it('Select a shipping method in checkout view', () => {
    cy.visit('/checkout');

    cy.get('.wc-block-components-address-form__email input').should('be.visible');
    cy.get('.wc-block-components-address-form__email input').should('have.value', '');
    cy.get('.wc-block-components-address-form__email input').type('test@test.com');

    shippingAddressFormBlocksFill({
      'shipping-first_name': 'Jean',
      'shipping-last_name': 'Némar',
      'shipping-address_1': '1 rue du Test Automatisé',
      'shipping-postcode': '45000',
      'shipping-city': 'Orléans'
    });

    selectRateInAccordion('Door Delivery', 'Chrono 18');

    cy.get('#shipping-hges-is-company-address').should('be.visible');
    cy.get('#shipping-hges-is-company-address').should('not.be.checked');
    cy.get('#shipping-hges-is-company-address').click();
    cy.get('#shipping-hges-is-company-address').should('be.checked');

    cy.get('#shipping-hges-company-name').should('be.visible');
    cy.get('#shipping-hges-company-name').should('have.value', '');
    cy.get('#shipping-hges-company-name').type('Test Company');
    cy.get('#shipping-hges-company-name').should('have.value', 'Test Company');

    cy.get('button.wc-block-components-checkout-place-order-button').should('be.visible');
    cy.get('button.wc-block-components-checkout-place-order-button').click();

    checkOrderConfirmationContent();
  });
});