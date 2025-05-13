import addToCart from "../support/addToCart";

describe('Cart spec', () => {

  beforeEach(() => {
    addToCart();
  });

  it('Select a shipping method in cart view', () => {
    cy.visit('/index.php/cart');
    const flatRateItem = cy.contains('Flat rate').should('be.visible');
    flatRateItem.click();
  });
});
