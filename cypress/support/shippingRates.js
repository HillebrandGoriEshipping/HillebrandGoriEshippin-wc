export const selectRateInAccordion = (headerTitle, methodName) => {
  cy.get('.wc-block-components-totals-shipping__fieldset .wc-block-components-loading-mask').should('not.exist');
  cy.log('Select shipping method in accordion', headerTitle, methodName);
  const doorDeliverySectionHeader = cy.get('span').contains(headerTitle).parents('button.accordion-header').should('be.visible');

  doorDeliverySectionHeader.then(($header) => {
    if ($header.hasClass('collapsed')) {
      cy.wrap($header).click();
    }

    cy.get('.shipping-rates .rate-content')
      .contains(methodName)
      .should('be.visible')
      .then(($label) => {
        const $rate = $label.closest('.rate-content');
        cy.wrap($rate).click().should('have.class', 'selected');
      });
  });


}