import { test } from '../helpers/fixtures';
import { expect } from '@playwright/test';
import setUiToClassic from '../../scripts/setUiToClassic.js';
import { addToCart } from '../helpers/cart';
import { formFillById } from '../helpers/formFill';
import { checkOrderConfirmationContent } from '../helpers/orderConfirmation';

test.describe('Classic UI Cart spec', () => {
    test.beforeAll(async () => {
        await setUiToClassic();
    });

    test.beforeEach(async ({ page }) => {
        await addToCart(page, 'classic');
    });

    test('Places order with custom address fields', async ({ page, customer }) => {
        await page.goto('/cart');
        await expect(page.getByText('Proceed to checkout')).toBeVisible();
        await page.getByText('Proceed to checkout').click();

        await expect(page.locator('h1')).toHaveText('Checkout');

        const formValues = {
            'billing_first_name': customer().firstName,
            'billing_last_name': customer().lastName,
            'billing_address_1': customer().address1,
            'billing_postcode': customer().postcode,
            'billing_city': customer().city,
            'billing_email': customer().email
        };

        await formFillById(page, formValues);

        const isCompanyCheckboxId = '#_wc_billing\\/hges\\/is-company-address';
        const companyNameId = '#_wc_billing\\/hges\\/company-name';

        const checkbox = page.locator(isCompanyCheckboxId);
        await expect(checkbox).toBeVisible();
        await expect(checkbox).not.toBeChecked();
        await checkbox.check();
        await expect(checkbox).toBeChecked();

        const companyNameField = page.locator(companyNameId);
        await expect(companyNameField).toBeVisible();
        await expect(companyNameField).toHaveValue('');
        await companyNameField.fill('Test Company');
        await expect(companyNameField).toHaveValue('Test Company');

        const flatRate = page.getByText('Flat rate');
        await expect(flatRate).toBeVisible();
        await flatRate.click();
        const label = await flatRate.locator('..').getAttribute('for');
        if (label) {
            await expect(page.locator(`#${label}`)).toBeChecked();
        }

        const placeOrderButton = page.locator('button[name="woocommerce_checkout_place_order"]');
        await expect(placeOrderButton).toBeVisible();
        await placeOrderButton.click();

        await checkOrderConfirmationContent(page, false);
    });

    test('Saves custom business order address fields', async ({ page, customer }) => {
        await page.goto('/checkout');

        const formValues = {
            'billing_first_name': customer().firstName,
            'billing_last_name': customer().lastName,
            'billing_address_1': customer().address1,
            'billing_postcode': customer().postcode,
            'billing_city': customer().city,
            'billing_email': customer().email,
            '_wc_billing/hges/is-company-address': customer(true).isCompanyAddress,
            '_wc_billing/hges/company-name': customer(true).companyName,
            '_wc_billing/hges/excise-number': customer(true).exciseNumber
        };

        await formFillById(page, formValues);

        const placeOrderButton = page.locator('button[name="woocommerce_checkout_place_order"]');
        await expect(placeOrderButton).toBeVisible();
        await placeOrderButton.click();

        await checkOrderConfirmationContent(page, true);
    });
});