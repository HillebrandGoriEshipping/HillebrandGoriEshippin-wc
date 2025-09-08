import { test, expect } from '@playwright/test';
import { formFillById } from '../helpers/formFill';
import { addToCart } from '../helpers/cart';
import { selectRateInAccordion } from '../helpers/shippingRates';
import setUiToBlocks from '../../scripts/setUiToBlocks';

test.describe('Block UI Checkout spec', () => {
    test.beforeAll(async () => {
        await setUiToBlocks();
    });

    test.beforeEach(async ({ page }) => {
        await addToCart(page, 'blocks');
    });

    test('Select a shipping method in checkout view', async ({ page }) => {
        await page.goto('/cart');
        await page.getByText('Proceed to Checkout').click();

        // await page.waitForTimeout(10000);

        // await page.waitForURL('**/checkout**');
        // await page.waitForLoadState('domcontentloaded');
        
        await expect(page.locator('h1')).toContainText('Checkout');

        const shippingAddressTitle = page.locator('h2', {hasText: 'Shipping address'});
        await expect(shippingAddressTitle).toBeVisible();

        const inputValues = {
            'shipping-first_name': 'Jean',
            'shipping-last_name': 'Némar',
            'shipping-address_1': '1 rue du Test Automatisé',
            'shipping-postcode': '45000',
            'shipping-city': 'Orléans'
        };

        formFillById(page, inputValues);

        const accordionHeader = page.locator('.accordion-header.open');
        await accordionHeader.waitFor({ state: 'visible' });

        // select a shipping method
        const shippingMethodFieldSet = await page.locator('.wp-block-woocommerce-checkout-shipping-methods-block');
        await selectRateInAccordion(page, shippingMethodFieldSet, 'Other shipping method', 'Flat rate');
        // then another
        await selectRateInAccordion(page, shippingMethodFieldSet, 'Door Delivery', 0);
    });
});