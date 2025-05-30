import addToCart from "../../support/addToCart";
import { selectRateInAccordion } from "../../support/shippingRates";
import { shippingAddressFormBlocksFill } from "../../support/formFill";
import { checkOrderConfirmationContent } from "../../support/orderConfirmation";

describe('Block UI Order spec', () => {
  before(() => {
    cy.task('setUiToBlocks');
  });
  
  beforeEach(() => {
    addToCart('blocks');
  });

  it('Select a shipping method in checkout view', () => {
    cy.visit('/checkout');

    cy.get('.wc-block-components-address-form__email input').should('be.visible');
    cy.get('.wc-block-components-address-form__email input').should('have.value', '');
    cy.get('.wc-block-components-address-form__email input').type('test@test.com');

    shippingAddressFormBlocksFill({
      'shipping-first_name': 'Jean',
      'shipping-last_name': 'Némar',
      'shipping-address_1': '1 rue du Test Automatisé',
      'shipping-postcode': '45000',
      'shipping-city': 'Orléans'
    });

    selectRateInAccordion('Door Delivery', 'Chrono 18');

    cy.get('#shipping-hges-is-company-address').should('be.visible');
    cy.get('#shipping-hges-is-company-address').should('not.be.checked');
    cy.get('#shipping-hges-is-company-address').click();
    cy.get('#shipping-hges-is-company-address').should('be.checked');

    cy.get('#shipping-hges-company-name').should('be.visible');
    cy.get('#shipping-hges-company-name').should('have.value', '');
    cy.get('#shipping-hges-company-name').type('Test Company');
    cy.get('#shipping-hges-company-name').should('have.value', 'Test Company');

    cy.get('button.wc-block-components-checkout-place-order-button').should('be.visible');
    cy.get('button.wc-block-components-checkout-place-order-button').click();

    checkOrderConfirmationContent();
  });


  it('Selects a pickup point in map', () => {
    cy.visit('/cart');

    cy.contains('Proceed to Checkout').click();
    cy.get('.wc-block-components-address-form__email input').should('be.visible');
    cy.get('.wc-block-components-address-form__email input').should('have.value', '');
    cy.get('.wc-block-components-address-form__email input').type('test@test.com');
    shippingAddressFormBlocksFill({
      'shipping-first_name': 'Jean',
      'shipping-last_name': 'Némar',
      'shipping-address_1': '1 rue du Test Automatisé',
      'shipping-postcode': '45000',
      'shipping-city': 'Orléans'
    });

    // select a pickup shipping method
    selectRateInAccordion('Pickup points', 0);

    cy.get('.rate-content.selected').then(($label) => {
      const pickupButton = cy.wrap($label).get('div.pickup-point-button > button').should('be.visible');
      pickupButton.click();
    });

    cy.get('#pickup-points-map-modal').should('be.visible');
    cy.get('#pickup-points-map').should('be.visible');
    cy.get('#pickup-points-list .pickup-point').should('be.visible').then($pickupPointItem => {
      cy.wrap($pickupPointItem).first().find('a').click();
    });

    cy.get('#pickup-points-list .pickup-point').should('be.visible').then($pickupPointItem => {
      cy.wrap($pickupPointItem).eq(1).find('a').click();
    });

    let thirdPickupPointName = '';
    cy.get('#pickup-points-list .pickup-point').should('be.visible').then($pickupPointItem => {
      thirdPickupPointName = $pickupPointItem.eq('2').find('a').text();
      cy.wrap($pickupPointItem).eq(2).find('a').click();

      cy.get('#pickup-points-map .marker-popup__title').should('be.visible').then($popup => {
        const popupText = $popup.text();
        expect(popupText).to.include(thirdPickupPointName);

        cy.get('button.pickup-point__select-btn').click();
      });
    });

    cy.get('#pickup-points-map-modal').should('not.be.visible');
    cy.get('.selected-pickup-point').should('be.visible').then($selectedPickupPoint => {
      const selectedText = $selectedPickupPoint.eq(0).text();
      expect(selectedText).to.include(thirdPickupPointName);
      cy.get('button').contains('Place Order').click(); 
      cy.get('.woocommerce-column--shipping-address address').contains(thirdPickupPointName).should('be.visible');
    });
  });
});