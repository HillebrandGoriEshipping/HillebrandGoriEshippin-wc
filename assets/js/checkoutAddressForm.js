const { select } = window.wp.data;
const cartStore = select('wc/store/cart');
const checkoutStore = select('wc/store/checkout');

let waitingForCalculation = false;

wp.data.subscribe(() => {
    const companyNameField = document.querySelector('.wc-block-components-address-form__hges-company-name');
    if (cartStore.isCustomerDataUpdating() && !waitingForCalculation) {
        waitingForCalculation = true;
        companyNameField.setAttribute('disabled');
    } 
    // if we were waiting for the calculation to end
    else if (!cartStore.isCustomerDataUpdating() && waitingForCalculation) {
        waitingForCalculation = false;
        companyNameField.removeAttribute('disabled');
        updateCompanyNameDisplay();
    }
});

const updateCompanyNameDisplay = () => {
    const isCompanyFieldVisible = !!cartStore.getCustomerData().billingAddress['hges/is-company-address'];
    companyNameField.style.display = isCompanyFieldVisible ? 'block' : 'none';
}
