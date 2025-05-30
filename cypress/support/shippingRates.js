/**
 * 
 * @param {string} headerTitle 
 * @param {string|number} method Method name or index (in the accordion children)
 */
export const selectRateInAccordion = (headerTitle, method) => {
  let methodName = method;
  if (typeof method === 'number') {
    methodName = cy.get('.shipping-rates .rate-content').eq(method).invoke('text').then(text => text.trim());
    cy.expect(methodName).to.not.be.empty;
  }
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