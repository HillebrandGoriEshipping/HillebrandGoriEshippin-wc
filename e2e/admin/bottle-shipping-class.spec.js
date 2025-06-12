import { test, expect } from '@playwright/test';
import adminLogin from '../helpers/adminLogin';

test.describe('Admin Bottle shipping class spec', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp/wp-admin/admin.php?page=wc-settings&tab=shipping&section=classes');
    await adminLogin(page);
  });

  test('Check bottle shipping class exists in shipping classes list', async ({ page }) => {
    const classNames = page.locator('.wc-shipping-class-name');
    await expect(classNames).toBeVisible();
    const bottleRow = classNames.locator('.view', { hasText: 'Bottle' });
    await expect(bottleRow).toBeVisible();
  });

  test('Check bottle shipping class exists in product settings', async ({ page }) => {
    await page.goto('/wp/wp-admin/post.php?post=40&action=edit');
    
    await page.waitForTimeout(5000);
    const shippingOptions = page.locator('.shipping_options');
    await expect(shippingOptions).toBeVisible();
    await shippingOptions.click();
    await expect(shippingOptions).toHaveClass(/active/);

    const shippingClassSelect = page.locator('#product_shipping_class');
    await expect(shippingClassSelect).toBeVisible();
    await shippingClassSelect.selectOption({ label: 'Bottle' });
    await expect(shippingClassSelect).toContainText('Bottle');
  });

});