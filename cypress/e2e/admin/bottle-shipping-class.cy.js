describe('Admin Bottle shipping class spec', () => {
  beforeEach(() => {
        cy.visit('/wp/wp-admin/admin.php?page=wc-settings&tab=shipping&section=classes');
        cy.get('#user_login').type('hges');
        cy.wait(1000);
        cy.get('#user_pass').type('hges');
        cy.get('#wp-submit').click();
    });
    
    it ('Check bottle shipping class exist in shipping classes list', () => {
        cy.get('.wc-shipping-class-name').should('be.visible');
        cy.get('.wc-shipping-class-name').find('.view').should('exist');
        cy.get('.wc-shipping-class-name').find('.view').contains('Bottle');
    });
    
    it ('Check bottle shipping class exist in product settings', () => {
        cy.visit('/wp/wp-admin/post.php?post=40&action=edit');
        cy.get('.shipping_options').should('be.visible');
        cy.get('.shipping_options').click();
        cy.get('.shipping_options').should('have.class', 'active');
        cy.get('#product_shipping_class').should('be.visible');
        cy.get('#product_shipping_class').select('Bottle');
        cy.get('#product_shipping_class').contains('Bottle');
    });

});