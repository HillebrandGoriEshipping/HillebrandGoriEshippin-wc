export default function() {
    cy.visit('/shop');

    cy.contains('h2', 'Clos des Murmures')
        .should('be.visible')
        .parents('li')
        .as('firstProduct');

    cy.get('@firstProduct')
        .should('be.visible')
        .within(() => {
            cy.contains('Add to cart').should('be.visible').click();
            cy.contains('View cart').should('be.visible');
        });

    cy.contains('La Goutte du Temps').click();
    cy.contains('h1', 'La Goutte du Temps').should('be.visible');
    cy.get('button[type="submit"]').contains('Add to cart').should('be.visible').click();

    cy.get('div.woocommerce-message')
        .should('be.visible')
        .find('a')
        .contains('View cart')
        .click();

    cy.contains('h1', 'Cart').should('be.visible');

    cy.get('.wc-block-cart-items__row').should('have.length', 2);

    const expectedUrl = Cypress.config('baseUrl') + '/product/clos-des-murmures/';

    cy.get('.wc-block-cart-items__row').first()
        .find('a')
        .should('have.attr', 'href')
        .then((href) => {
            expect(href).to.eq(expectedUrl);
        });
}

