export const shippingAddressFormBlocksFill = (data) => {
    cy.get('.wp-block-woocommerce-checkout-shipping-address-block input')
        .each(($input) => {
            const id = $input.attr('id');
            const value = data[id];

            if (value !== undefined) {
                cy.wrap($input).clear().type(value);
            }
        });
}

export const shippingAddressFormClassicFill = (data) => {
    cy.get('.woocommerce-billing-fields input')
        .each(($input) => {
            const id = $input.attr('id');
            const value = data[id];

            if (value !== undefined) {
                cy.wrap($input).clear().type(value);
            }
        });
}