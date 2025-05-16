export const selectRateInAccordion = (headerTitle, methodName) => {
    const doorDeliverySectionHeader = cy.get('span').contains(headerTitle).parents('button.accordion-header').should('be.visible');
    doorDeliverySectionHeader.click();

    cy.get('.shipping-rates .rate-content')
    .contains(methodName)
    .should('be.visible')
    .then(($label) => {
      const $rate = $label.closest('.rate-content');
      cy.wrap($rate).click().should('have.class', 'selected');
    });
}