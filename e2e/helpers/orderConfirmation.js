import { expect }from '@playwright/test';

export async function checkOrderConfirmationContent(page, isBusinessOrder) {
  await expect(page).toHaveURL(/\/order-received/);
  const heading = page.locator('h1');
  await expect(heading).toHaveText('Order received');

  if (isBusinessOrder) {
    const addressColumn = page.locator('.woocommerce-column--shipping-address');

    await expect(addressColumn.locator('dt', { hasText: 'Business order' })).toBeVisible();
    await expect(addressColumn.locator('dd', { hasText: 'Yes' })).toBeVisible();

    await expect(addressColumn.locator('dt', { hasText: 'Company name' })).toBeVisible();
    await expect(addressColumn.locator('dd', { hasText: 'Test Company' })).toBeVisible();

    await expect(addressColumn.locator('dt', { hasText: 'Excise number' })).toBeVisible();
    await expect(addressColumn.locator('dd', { hasText: '12345678901234' })).toBeVisible();
  }

  return expect(page.locator('.woocommerce-order-overview')).toBeVisible();
};