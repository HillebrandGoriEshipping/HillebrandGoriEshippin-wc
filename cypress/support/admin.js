export async function login(username, password) {
    cy.visit('/wp/wp-login.php');
    cy.get('#user_login').type(username);
    cy.wait(1000);
    cy.get('#user_pass').type(password);
    cy.get('#wp-submit').click();
}