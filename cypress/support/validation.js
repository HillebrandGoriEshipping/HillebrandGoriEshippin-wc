export function checkFieldValidation(field, incorrectValue, correctValue, expectedErrorMessage) {
    cy.get(field).clear().type(incorrectValue);
    cy.get(field).parents('form').submit();
    cy.get(field).parents('td').find('.error-message').should('be.visible').then(($error) => {
        expect($error.text().trim()).to.equal(expectedErrorMessage);
    });

    cy.get(field).focus();
    cy.get(field).clear().type(correctValue);
    cy.get(field).parents('form').submit();
    cy.get(field).parents('td').find('.error-message').should('not.exist');
    cy.get(field).should('have.value', correctValue);
}