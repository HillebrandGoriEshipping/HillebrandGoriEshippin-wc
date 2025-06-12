import { test, expect } from '@playwright/test';
import messages from '../../assets/js/config/messages.json';
import adminLogin from '../helpers/adminLogin';

test.describe('Admin Product Meta spec', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp/wp-admin/post.php?post=39&action=edit');
    await adminLogin(page);
  });

  test('Check product meta form message success', async ({ page }) => {
    await expect(page.locator('#product-type')).toBeVisible();
    await page.selectOption('#product-type', 'bottle-simple');

    await expect(page.locator('.general_tab')).toBeVisible();
    await expect(page.locator('.HGeS_product_tab_options')).toBeVisible();

    await page.click('.HGeS_product_tab_options');
    await expect(page.locator('.HGeS_product_tab_options')).toHaveClass(/active/);

    await expect(page.locator('#error-container')).toBeHidden();

    await expect(page.locator('#_color')).toBeVisible();
    await page.selectOption('#_color', 'White');

    await expect(page.locator('#_alcohol_percentage')).toBeVisible();
    const alcoholInput = page.locator('input#_alcohol_percentage');
    await expect(alcoholInput).toHaveValue('13');
    await expect(alcoholInput).toHaveAttribute('type', 'number');
    await expect(alcoholInput).toHaveAttribute('step', '0.1');

    await expect(page.locator('#_capacity')).toBeVisible();
    const capacityInput = page.locator('input#_capacity');
    await expect(capacityInput).toHaveValue('1500');
    await expect(capacityInput).toHaveAttribute('type', 'number');
    await expect(capacityInput).toHaveAttribute('step', '1');

    await expect(page.locator('#_producing_country')).toBeVisible();
    await page.selectOption('#_producing_country', 'France');

    await page.selectOption('#_appellation', 'Chablis');

    await expect(page.locator('#error-container')).toBeVisible();
    await expect(page.locator('#error-container')).toContainText(messages.productMeta.settingsSuccess);
  });

  test('Check product meta form message error', async ({ page }) => {
    await expect(page.locator('#product-type')).toBeVisible();
    await page.selectOption('#product-type', 'bottle-variable');

    await expect(page.locator('.general_tab')).toBeHidden();
    await expect(page.locator('.HGeS_product_tab_options')).toBeVisible();

    await expect(page.locator('.variations_tab')).toBeVisible();
    await page.click('.variations_tab');
    await expect(page.locator('.variations_tab')).toHaveClass(/active/);

    await page.locator('.edit_variation').first().click();
    await expect(page.locator('#variation_quantity_0')).toBeVisible();

    await page.click('.HGeS_product_tab_options');
    await expect(page.locator('.HGeS_product_tab_options')).toHaveClass(/active/);

    await expect(page.locator('#error-container')).toBeHidden();

    await expect(page.locator('#_color')).toBeVisible();
    await page.selectOption('#_color', 'White');

    await expect(page.locator('#_alcohol_percentage')).toBeVisible();
    const alcoholInput = page.locator('input#_alcohol_percentage');
    await expect(alcoholInput).toHaveValue('13');
    await expect(alcoholInput).toHaveAttribute('type', 'number');
    await expect(alcoholInput).toHaveAttribute('step', '0.1');

    await expect(page.locator('#_capacity')).toBeVisible();
    const capacityInput = page.locator('input#_capacity');
    await expect(capacityInput).toHaveValue('1500');
    await expect(capacityInput).toHaveAttribute('type', 'number');
    await expect(capacityInput).toHaveAttribute('step', '1');

    await expect(page.locator('#_producing_country')).toBeVisible();
    await page.selectOption('#_producing_country', 'France');

    await page.selectOption('#_appellation', 'Hydromel');

    await expect(page.locator('#error-container')).toBeVisible();
    await expect(page.locator('#error-container')).toContainText(messages.productMeta.settingsError);
  });
});