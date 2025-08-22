import base from '@playwright/test'

export const test = base.extend({
    customer: async ({ }, use) => {
        const getCustomer = (isCompany = false) => {
            const customerData = {
                email: 'test@test.com',
                firstName: 'Jean',
                lastName: 'Némar',
                address1: '1 rue du Test Automatisé',
                postcode: '29200',
                city: 'Brest'
            };

            if (isCompany) {
                customerData.isCompanyAddress = true;
                customerData.companyName = 'Test Company';
                customerData.exciseNumber = '12345678901234';
            }

            return customerData;
        };

        // On expose la factory via `use`
        await use(getCustomer);
    }
});