import { test, expect } from '@playwright/test';
import messages from '../../assets/js/config/messages.json';
import adminLogin from '../helpers/adminLogin';

test.describe('Api key validation spec', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/wp/wp-admin/admin.php?page=hillebrand-gori-eshipping');
    await adminLogin(page);
  });

  test('Check api key validation success', async ({ page }) => {
    const apiInput = page.locator('#api-input');
    const validateButton = page.locator('#validate-api');

    await expect(apiInput).toBeVisible();
    await apiInput.fill(process.env.HGES_API_KEY);
    await validateButton.click();

    await expect(apiInput).toHaveClass(/valid/);
    await expect(page.locator('.notice-success')).toBeVisible();
    await expect(page.locator('.notice-success')).toContainText(messages.apiKeyValidation.apiKeySuccess);
  });

  test('Check api key validation error', async ({ page }) => {
    const apiInput = page.locator('#api-input');
    const validateButton = page.locator('#validate-api');

    await expect(apiInput).toBeVisible();
    await apiInput.fill('12345');
    await validateButton.click();

    await expect(apiInput).toHaveClass(/invalid/);
    await expect(page.locator('.notice-error')).toBeVisible();
    await expect(page.locator('.notice-error')).toContainText(messages.apiKeyValidation.apiKeyError);
  });

});
