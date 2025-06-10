import { test, expect } from '@playwright/test';
import messages from '../../assets/js/config/messages.json';

test.describe('Api key validation spec', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/wp/wp-admin/admin.php?page=hillebrand-gori-eshipping');
    await page.locator('#user_login').fill('hges');
    const passwordInput = page.locator('#user_pass');
    await passwordInput.waitFor();
    await passwordInput.fill('hges');
    await page.locator('#wp-submit').click();
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
