import { login } from '../../support/admin.js';

describe('Admin HGeS settings page spec', () => {
    beforeEach(() => {
        login('hges', 'hges');
    });
    
    it('Api Key upadate', () => {
        cy.visit('/wp/wp-admin/admin.php?page=hillebrand-gori-eshipping');
        cy.get('h1').contains('Hillebrand Gori eShipping Settings').should('be.visible');
        cy.get('#api-input').should('be.visible');
        cy.get('#api-input').clear().type('wrongapikey');
        cy.get('#validate-api').should('be.visible');
        cy.get('#validate-api').click();
        cy.get('.notice-error p').contains('API Key is invalid! Please try again').should('be.visible');


        cy.get('#api-input').clear().type('+jc0wVA/fprjeEcbG6gWcl4FNhMJCTs3Y29VeysSb5PNvAxJPERBjfL/RG1736wvEkg=');
        cy.get('#validate-api').click();
        cy.get('.notice-success p').contains('API Key is valid!').should('be.visible');

        cy.get('#apikey-settings-form').submit();
         cy.get('#api-input').should('have.value', '+jc0wVA/fprjeEcbG6gWcl4FNhMJCTs3Y29VeysSb5PNvAxJPERBjfL/RG1736wvEkg=');
    });
});