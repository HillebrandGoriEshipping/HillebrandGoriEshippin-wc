import messages from "../../../assets/js/config/messages.json";

describe('Api key validation spec', () => {
  beforeEach(() => {
    cy.visit('/wp/wp-admin/admin.php?page=hillebrand-gori-eshipping');
    cy.get('#user_login').type('hges');
    cy.wait(1000);
    cy.get('#user_pass').type('hges');
    cy.get('#wp-submit').click();
  });
 
  it ('Check api key validation success', () => {
    cy.get('#api-input').should('be.visible');
    cy.get('#api-input').clear();
    cy.get('#api-input').type('+jc0wVA/fprjeEcbG6gWcl4FNhMJCTs3Y29VeysSb5PNvAxJPERBjfL/RG1736wvEkg=');
    cy.get('#validate-api').click();
    cy.get('#api-input').should('have.class', 'valid');
    cy.get('.notice-success').should('be.visible');
    cy.get('.notice-success').contains(messages.apiKeyValidation.apiKeySuccess);
  });

    it ('Check api key validation error', () => {
        cy.get('#api-input').should('be.visible');
        cy.get('#api-input').clear();
        cy.get('#api-input').type('12345');
        cy.get('#validate-api').click();
        cy.get('#api-input').should('have.class', 'invalid');
        cy.get('.notice-error').should('be.visible');
        cy.get('.notice-error').contains(messages.apiKeyValidation.apiKeyError);
    });
});