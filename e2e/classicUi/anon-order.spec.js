import { test, expect } from '@playwright/test';
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

    test('Places order with custom address fields', async ({ page }) => {
        await page.goto('/cart');
        await expect(page.getByText('Proceed to checkout')).toBeVisible();
        await page.getByText('Proceed to checkout').click();

        await expect(page.locator('h1')).toHaveText('Checkout');

        const formValues = {
            'billing_first_name': 'Jean',
            'billing_last_name': 'Némar',
            'billing_address_1': '1 rue du Test Automatisé',
            'billing_postcode': '45000',
            'billing_city': 'Orléans',
            'billing_email': 'test@test.com'
        };

        await formFillById(page, formValues);

        const isCompanyCheckboxId = '#wc_billing\\/hges\\/is-company-address';
        const companyNameId = '#wc_billing\\/hges\\/company-name';

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

    test('Saves custom business order address fields', async ({ page }) => {
        await page.goto('/checkout');

        const formValues = {
            'billing_first_name': 'Jean',
            'billing_last_name': 'Némar',
            'billing_address_1': '1 rue du Test Automatisé',
            'billing_postcode': '45000',
            'billing_city': 'Orléans',
            'billing_email': 'test@test.com',
            'wc_billing/hges/is-company-address': true,
            'wc_billing/hges/company-name': 'Test Company',
            'wc_billing/hges/excise-number': '12345678901234'
        };

        await formFillById(page, formValues);

        const placeOrderButton = page.locator('button[name="woocommerce_checkout_place_order"]');
        await expect(placeOrderButton).toBeVisible();
        await placeOrderButton.click();

        await checkOrderConfirmationContent(page, true);
    });
});