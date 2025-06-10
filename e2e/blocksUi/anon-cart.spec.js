import { test, expect } from '@playwright/test';
import { addToCart, blocksFillDeliveryAddress } from '../helpers/cart';
import { selectRateInAccordion } from '../helpers/shippingRates';
import setUiToBlocks from '../../scripts/setUiToBlocks';

test.describe('Block UI Cart spec', () => {
  test.beforeAll(async () => {
    await setUiToBlocks();
  });

  test.beforeEach(async ({ page }) => {
    await addToCart(page, 'blocks');
  });

  test('Select a shipping method in cart view', async ({ page }) => {
    await page.goto('/cart');
    await expect(page.locator('h1')).toContainText('Cart');
    await blocksFillDeliveryAddress(page);

    await selectRateInAccordion(page, 'Door Delivery', 0);
    await selectRateInAccordion(page, 'Other shipping method', 'Flat rate');

    console.log('Anon Cart spec done');
  });

  test('Select a pickup delivery mode shipping method in checkout view', async ({ page }) => {
    await page.goto('/cart');
    await expect(page.locator('h1')).toContainText('Cart');
    await blocksFillDeliveryAddress(page);


    await selectRateInAccordion(page, 'Pickup points', 0);
 
    const selectedRate = page.locator('.rate-content.selected');
    await expect(selectedRate.locator('div.pickup-point-button > button')).toHaveCount(0);
  });

  test('Remove items from cart', async ({ page }) => {
    await page.goto('/cart');
    await expect(page.locator('h1')).toContainText('Cart');

    const removeLinks = await page.locator('.wc-block-cart-item__remove-link');
    const count = await removeLinks.count();
    for (let i = 0; i < count; i++) {
      await removeLinks.nth(0).click();
      await page.waitForTimeout(500);
    }

    await page.waitForTimeout(10000);
    await expect(page.locator('.wp-block-woocommerce-empty-cart-block')).toBeVisible();
  });
});