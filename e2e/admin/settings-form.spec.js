import { test, expect } from '@playwright/test';
import messages from '../../assets/js/config/messages.json';
import adminLogin from '../helpers/adminLogin';
import { checkFieldValidation } from '../helpers/validation';

test.describe('Admin HGeS settings page spec', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp/wp-admin/admin.php?page=hillebrand-gori-eshipping');
    await adminLogin(page);  
  });

  test('Api Key update', async ({ page }) => {
    await expect(page.locator('h1')).toContainText('Hillebrand Gori eShipping Settings');
    await expect(page.locator('#api-input')).toBeVisible();

    await page.fill('#api-input', 'wrongapikey');
    await expect(page.locator('#validate-api')).toBeVisible();
    await page.click('#validate-api');
    await expect(page.locator('.notice-error p')).toContainText('API Key is invalid! Please try again');

    const validKey = process.env.HGES_API_KEY;
    await page.fill('#api-input', validKey);
    await page.click('#validate-api');
    await expect(page.locator('.notice-success p')).toContainText('API Key is valid!');

    await page.locator('#apikey-settings-form').evaluate(form => form.submit());
    await expect(page.locator('#api-input')).toHaveValue(validKey);
  });

  test('Configuration form', async ({ page }) => {
    await page.goto('/wp/wp-admin/admin.php?page=hillebrand-gori-eshipping');
    await expect(page.locator('#address-table')).toBeVisible();

    const rows = page.locator('#address-table tbody tr');
    await expect(rows.nth(0).locator('td')).toContainText('Adresse principale');
    await expect(rows.nth(1).locator('td')).toContainText('MY COMPANY');
    await expect(rows.nth(2).locator('td')).toContainText('John DOE');
    await expect(rows.nth(3).locator('td')).toContainText('33102030405');
    await expect(rows.nth(4).locator('td')).toContainText('69B rue du Colombier');
    await expect(rows.nth(5).locator('td')).toContainText('Orléans');

    await checkFieldValidation(page, 'input[name="HGES_VAT_NUMBER"]', 'invalid_vat_number', 'FR123456789', messages.settings.vatNumberError);
    await checkFieldValidation(page, 'input[name="HGES_EORI_NUMBER"]', 'invalid_eori_number', 'FR123456789', messages.settings.eoriNumberError);
    await checkFieldValidation(page, 'input[name="HGES_FDA_NUMBER"]', 'invalid_fda_number', '12345678901', messages.settings.fdaNumberError);
  });
});