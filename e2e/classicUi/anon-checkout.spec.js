import { test } from '../helpers/fixtures';
import { expect } from '@playwright/test';
import setUiToClassic from '../../scripts/setUiToClassic.js';
import { addToCart } from '../helpers/cart';
import { formFillById } from '../helpers/formFill';
import { selectRateByName } from '../helpers/shippingRates';

test.describe('Classic UI Cart spec', () => {
    test.beforeAll(async () => {
        setUiToClassic();
    });

    test.beforeEach(async ({ page }) => {
        await addToCart(page, 'classic');
    });

    test('Go to checkout from cart and select rate', async ({ page, customer }) => {
        await page.goto('/cart');

        const proceedToCheckout = page.getByRole('link', { name: 'Proceed to checkout' });
        await expect(proceedToCheckout).toBeVisible();
        await proceedToCheckout.click();

        await expect(page.locator('h1')).toHaveText('Checkout');

        const inputValues = {
            'billing_first_name': customer().firstName,
            'billing_last_name': customer().lastName,
            'billing_address_1': customer().address1,
            'billing_postcode': customer().postcode,
            'billing_city': customer().city,
            'billing_email': customer().email
        };
        await formFillById(page, inputValues);

        await selectRateByName(page, 'UPS Standard');
        await selectRateByName(page, 'Flat rate');
    });
});
