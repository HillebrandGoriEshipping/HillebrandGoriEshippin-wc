import { login } from '../../support/admin.js';
import messages from '../../../assets/js/config/messages.json';
import { checkFieldValidation } from '../../support/validation.js';

describe('Admin HGeS settings page spec', () => {
    beforeEach(() => {
        login('hges', 'hges');
    });
    
    it('Api Key update', () => {
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

    it ('Configuration form', () => {
        cy.visit('/wp/wp-admin/admin.php?page=hillebrand-gori-eshipping');
        cy.get('#address-table').should('be.visible');
        cy.get('#address-table').find('tbody tr').then((rows) => {
            cy.wrap(rows).eq(0).find('td').contains('Adresse principale').should('be.visible');
            cy.wrap(rows).eq(1).find('td').contains('MY COMPANY').should('be.visible');
            cy.wrap(rows).eq(2).find('td').contains('John DOE').should('be.visible');
            cy.wrap(rows).eq(3).find('td').contains('33102030405').should('be.visible');
            cy.wrap(rows).eq(4).find('td').contains('69B rue du Colombier').should('be.visible');
            cy.wrap(rows).eq(5).find('td').contains('Orléans').should('be.visible');
        });

        checkFieldValidation(
            'input[name="HGES_VAT_NUMBER"]',
            'invalid_vat_number',
            'FR123456789',
            messages.settings.vatNumberError
        );

        checkFieldValidation(
            'input[name="HGES_EORI_NUMBER"]',
            'invalid_eori_number',
            'FR123456789',
            messages.settings.eoriNumberError
        );
        
        checkFieldValidation(
            'input[name="HGES_FDA_NUMBER"]',
            'invalid_fda_number',
            '12345678901',
            messages.settings.fdaNumberError
        );
    });
});