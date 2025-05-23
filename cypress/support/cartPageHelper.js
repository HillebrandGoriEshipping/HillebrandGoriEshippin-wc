export const blocksFillDeliveryAddress = () => {
    // Define a country and a city to enable the display of the shipping rates
    const cartShippingFormButton = cy.get('.wc-block-components-totals-shipping-panel > div[role="button"]').should('be.visible');
    cartShippingFormButton.click();
    cy.get('.wc-block-components-address-form__postcode input').type('45000');
    cy.get('.wc-block-components-address-form__city input').type('Orléans');
    cy.get('form.wc-block-components-shipping-calculator-address .wc-block-components-shipping-calculator-address__button').click();
    cy.wait(10000);
};