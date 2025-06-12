import { test, expect } from '@playwright/test';
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

    test('Go to checkout from cart and select rate', async ({ page }) => {
        await page.goto('/cart');

        const proceedToCheckout = page.getByRole('link', { name: 'Proceed to checkout' });
        await expect(proceedToCheckout).toBeVisible();
        await proceedToCheckout.click();

        await expect(page.locator('h1')).toHaveText('Checkout');

        const inputValues = {
            'billing_first_name': 'Jean',
            'billing_last_name': 'Némar',
            'billing_address_1': '1 rue du Test Automatisé',
            'billing_postcode': '29200',
            'billing_city': 'BREST',
            'billing_email': 'test@test.com'
        };
        await formFillById(page, inputValues);

        await selectRateByName(page, 'DHL DOMESTIC EXPRESS');
        await selectRateByName(page, 'Flat rate');
    });
});
