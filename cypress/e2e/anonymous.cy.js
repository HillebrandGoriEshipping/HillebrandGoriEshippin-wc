import addToCart from "../support/addToCart";

describe('Cart spec', () => {

  beforeEach(() => {
    addToCart();
  });

  it('Select a shipping method in cart view', () => {
    cy.visit('/cart');

    const cartShippingFormButton = cy.get('.wc-block-components-totals-shipping-panel > div[role="button"]').should('be.visible');
    cartShippingFormButton.click();
    
    cy.get('.wc-block-components-address-form__postcode input').type('45000');
    cy.get('.wc-block-components-address-form__city input').type('Orléans');

    cy.get('form.wc-block-components-shipping-calculator-address .wc-block-components-shipping-calculator-address__button').click();

    const doorDeliverySectionHeader = cy.get('span').contains('Door Delivery').parents('button.accordion-header').should('be.visible');
    doorDeliverySectionHeader.click();

    const chrono18RateElement = cy.contains('Chrono 18H').should('be.visible');
    chrono18RateElement.click();
  });
});
