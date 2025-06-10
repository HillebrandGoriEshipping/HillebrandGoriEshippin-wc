import { expect } from '@playwright/test';

async function checkCartBlocks(page) {
    const rows = page.locator('.wc-block-cart-items__row');
    await expect(rows).toHaveCount(2);

    const baseUrl = page.context()._options.baseURL || ''; // récupère baseURL Playwright
    const expectedUrl = baseUrl + '/product/clos-des-murmures/';

    const firstLink = rows.first().locator('a').first();
    await expect(firstLink).toHaveAttribute('href', expectedUrl);
}

async function checkCartClassic(page) {
    const rows = page.locator('tr.woocommerce-cart-form__cart-item.cart_item');
    await expect(rows).toHaveCount(2);

    const baseUrl = page.context()._options.baseURL || '';
    const expectedUrl = baseUrl + '/product/clos-des-murmures/';

    const firstLink = rows.first().locator('.product-name a').first();
    await expect(firstLink).toHaveAttribute('href', expectedUrl);
}

export async function addToCart(page, uiType) {
    await page.goto('/shop');

    const firstProduct = page.locator('h2', { hasText: 'Clos des Murmures' }).locator('xpath=ancestor::li');
    await expect(firstProduct).toBeVisible();

    await firstProduct.locator('text=Add to cart').click();
    await expect(page.locator('text=View cart')).toBeVisible();

    await page.click('text=La Goutte du Temps');
    await expect(page.locator('h1', { hasText: 'La Goutte du Temps' })).toBeVisible();

    await page.locator('button[type="submit"]', { hasText: 'Add to cart' }).click();

    const wooMessage = page.locator('div.woocommerce-message');
    await expect(wooMessage).toBeVisible();

    await wooMessage.locator('a', { hasText: 'View cart' }).click();

    await expect(page.locator('h1', { hasText: 'Cart' })).toBeVisible();

    if (uiType === 'classic') {
        await checkCartClassic(page);
    } else if (uiType === 'blocks') {
        await checkCartBlocks(page);
    }
}

export async function blocksFillDeliveryAddress(page) {
    const cartShippingFormButton = page.locator('.wc-block-components-totals-shipping-panel > div[role="button"]');
    await expect(cartShippingFormButton).toBeVisible();
    await cartShippingFormButton.click();

    await page.locator('.wc-block-components-address-form__postcode input').fill('45000');
    await page.locator('.wc-block-components-address-form__city input').fill('Orléans');

    await page.locator('form.wc-block-components-shipping-calculator-address .wc-block-components-shipping-calculator-address__button').click();

    await page.waitForTimeout(10000);
};