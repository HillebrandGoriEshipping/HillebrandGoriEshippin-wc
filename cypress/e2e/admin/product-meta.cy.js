import messages from "../../../assets/js/config/messages.json";

describe('Admin Product Meta spec ', () => {
  beforeEach(() => {
    cy.visit('/wp/wp-admin/post.php?post=39&action=edit');
    cy.get('#user_login').type('hges');
    cy.wait(1000);
    cy.get('#user_pass').type('hges');
    cy.get('#wp-submit').click();
  });
  
  it ('Check product meta form message success', () => {
    cy.get('#product-type').should('be.visible');
    cy.get('#product-type').select('bottle-simple');
    cy.get('.general_tab').should('be.visible');
    cy.get('.HGeS_product_tab_options').should('be.visible');
    cy.get('.HGeS_product_tab_options').click();
    cy.get('.HGeS_product_tab_options').should('have.class', 'active');
    cy.get('#error-container').should('not.be.visible');

    cy.get('#_color').should('be.visible');
    cy.get('#_color').select('White');

    cy.get('#_alcohol_percentage').should('be.visible');
    cy.get('input#_alcohol_percentage').should('have.value', '13');
    cy.get('input#_alcohol_percentage').should('have.attr', 'type', 'number');
    cy.get('input#_alcohol_percentage').should('have.attr', 'step', '0.1');

    cy.get('#_capacity').should('be.visible');
    cy.get('input#_capacity').should('have.value', '1500');
    cy.get('input#_capacity').should('have.attr', 'type', 'number');
    cy.get('input#_capacity').should('have.attr', 'step', '1');

    cy.get('#_producing_country').should('be.visible');
    cy.get('#_producing_country').select('France');

    cy.get('#_appellation').select('Chablis');
    cy.get('#error-container').should('be.visible');
    cy.get('#error-container').contains(messages.productMeta.settingsSuccess);
  });

  it ('Check product meta form message error', () => {
    cy.get('#product-type').should('be.visible');
    cy.get('#product-type').select('bottle-variable');
    cy.get('.general_tab').should('not.be.visible');
    cy.get('.HGeS_product_tab_options').should('be.visible');

    cy.get('.variations_tab').should('be.visible');
    cy.get('.variations_tab').click();
    cy.get('.variations_tab').should('have.class', 'active');
    cy.get('.edit_variation').first().click();
    cy.get('#variation_quantity_0').should('be.visible');

    cy.get('.HGeS_product_tab_options').click();
    cy.get('.HGeS_product_tab_options').should('have.class', 'active');
    cy.get('#error-container').should('not.be.visible');

    cy.get('#_color').should('be.visible');
    cy.get('#_color').select('White');

    cy.get('#_alcohol_percentage').should('be.visible');
    cy.get('input#_alcohol_percentage').should('have.value', '13');
    cy.get('input#_alcohol_percentage').should('have.attr', 'type', 'number');
    cy.get('input#_alcohol_percentage').should('have.attr', 'step', '0.1');

    cy.get('#_capacity').should('be.visible');
    cy.get('input#_capacity').should('have.value', '1500');
    cy.get('input#_capacity').should('have.attr', 'type', 'number');
    cy.get('input#_capacity').should('have.attr', 'step', '1');

    cy.get('#_producing_country').should('be.visible');
    cy.get('#_producing_country').select('France');

    cy.get('#_appellation').select('Hydromel');
    cy.get('#error-container').should('be.visible');
    cy.get('#error-container').contains(messages.productMeta.settingsError);
  });
});