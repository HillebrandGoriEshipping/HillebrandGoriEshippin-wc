let data;

export const shippingAddressFormFill = (uiMode, formData) => {
    data = formData;
    let inputSelector = '.wp-block-woocommerce-checkout-shipping-address-block input';
    if (uiMode === 'classic') {
        inputSelector = '.woocommerce-billing-fields input'
    }

    cy.get(inputSelector).each(fillForm);
}

function fillForm($input) {
    const id = $input.attr('id');
    const value = data[id];

    if (value === undefined) {
        return;
    }

    switch ($input.attr('type')) {
        case 'checkbox':
            if (value) {
                cy.wrap($input).check();
            } else {
                cy.wrap($input).uncheck();
            }
            return;
        default:
            cy.wrap($input).clear({force: true}).type(value);
            break;
    }
}
