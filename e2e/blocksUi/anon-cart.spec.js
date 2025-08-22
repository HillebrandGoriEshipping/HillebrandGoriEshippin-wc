import { test, expect } from '@playwright/test';
import { addToCart } from '../helpers/cart';
import setUiToBlocks from '../../scripts/setUiToBlocks';

test.describe('Block UI Cart spec', () => {
  test.beforeAll(async () => {
    await setUiToBlocks();
  });

  test.beforeEach(async ({ page }) => {
    await addToCart(page, 'blocks');
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

    await page.waitForTimeout(15000);
    await expect(page.locator('.wp-block-woocommerce-empty-cart-block')).toBeVisible();
  });
});