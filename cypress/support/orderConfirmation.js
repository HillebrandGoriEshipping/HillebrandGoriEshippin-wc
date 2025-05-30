export const checkOrderConfirmationContent = (isBusinessOrder) => {
    cy.get('h1').should('have.text', 'Order received');

    if (isBusinessOrder) { 
      (["shipping"]).forEach((addressType) => {
        cy.get(`.woocommerce-column--${addressType}-address`).then(addressColumn => {
          cy.wrap(addressColumn).find('dt').contains('Business order').should('be.visible');
          cy.wrap(addressColumn).find('dd').contains('Yes').should('be.visible');
          cy.wrap(addressColumn).find('dt').contains('Company name').should('be.visible');
          cy.wrap(addressColumn).find('dd').contains('Test Company').should('be.visible');
        });
      });
    }
}