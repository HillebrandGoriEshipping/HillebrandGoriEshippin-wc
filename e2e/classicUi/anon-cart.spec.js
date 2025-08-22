import { test } from '../helpers/fixtures';
import { expect } from '@playwright/test';
import { addToCart } from '../helpers/cart';
import { formFillById } from '../helpers/formFill';
import setUiToClassic from '../../scripts/setUiToClassic.js';

test.describe('Classic UI Cart spec', () => {
    test.beforeAll(async () => {
        setUiToClassic();
    });

    test.beforeEach(async ({ page }) => {
        await addToCart(page, 'classic');
    });

    test('Select a shipping method in cart view', async ({ page, customer }) => {
        await page.goto('/cart');

        const cartCalculateShippingLink = page.getByRole('button', { name: 'Calculate shipping' });
        await expect(cartCalculateShippingLink).toBeVisible();
        await cartCalculateShippingLink.click();

        const formValues = {
            'calc_shipping_city': customer().city,
            'calc_shipping_postcode': customer().postcode
        };
        await formFillById(page, formValues);

        await page.locator('.shipping-calculator-form button').click();
        await expect(page.locator('.woocommerce-shipping-methods h5', { hasText: 'Door Delivery'})).toBeVisible();
    });
});
