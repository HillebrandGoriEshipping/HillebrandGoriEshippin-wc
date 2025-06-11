
import { test, expect } from '@playwright/test';
import setUiToBlocks from '../../scripts/setUiToBlocks';
import { selectRateInAccordion } from '../helpers/shippingRates';
import { checkOrderConfirmationContent } from '../helpers/orderConfirmation';
import { addToCart } from '../helpers/cart';
import { formFillById } from '../helpers/formFill';

test.describe('Block UI Order spec', () => {
    test.beforeAll(async () => {
        await setUiToBlocks();
    });

    test.beforeEach(async ({ page }) => {
        await addToCart(page, 'blocks');
    });

    test('Select a shipping method in checkout view', async ({ page }) => {
        await page.goto('/checkout');

        const emailInput = page.locator('.wc-block-components-address-form__email input');
        await expect(emailInput).toBeVisible();
        await expect(emailInput).toHaveValue('');
        await emailInput.fill('test@test.com');

        const inputValues = {
            'shipping-first_name': 'Jean',
            'shipping-last_name': 'Némar',
            'shipping-address_1': '1 rue du Test Automatisé',
            'shipping-postcode': '45000',
            'shipping-city': 'Orléans'
        };
        
        formFillById(page, inputValues);

        const shippingAddressFieldset = page.locator('.wp-block-woocommerce-checkout-shipping-methods-block');
        await selectRateInAccordion(page, shippingAddressFieldset, 'Door Delivery', 'Chrono 13h');

        const placeOrderBtn = page.locator('button.wc-block-components-checkout-place-order-button');
        await expect(placeOrderBtn).toBeVisible();
        await placeOrderBtn.click();
        await checkOrderConfirmationContent(page, false);
    });

    test('Selects a pickup point in map', async ({ page }) => {
        await page.goto('/cart');
        await page.getByText('Proceed to Checkout').click();

        const emailInput = page.locator('.wc-block-components-address-form__email input');
        await expect(emailInput).toBeVisible();
        await expect(emailInput).toHaveValue('');
        await emailInput.fill('test@test.com');

        const inputValues = {
            'shipping-first_name': 'Jean',
            'shipping-last_name': 'Némar',
            'shipping-address_1': '1 rue du Test Automatisé',
            'shipping-postcode': '45000',
            'shipping-city': 'Orléans'
        };
        
        formFillById(page, inputValues);

        const pickupButton = page.locator('.rate-content.selected div.pickup-point-button > button');
        await expect(pickupButton).toBeVisible();
        await pickupButton.click();

        await expect(page.locator('#pickup-points-map-modal')).toBeVisible();
        await expect(page.locator('#pickup-points-map')).toBeVisible();
        await expect(page.locator('.modal__load-mask ')).not.toBeVisible();
        const pickupPoints = page.locator('#pickup-points-list .pickup-point');
        const pickupPointsCount = await pickupPoints.count();
        expect(pickupPointsCount).toBeGreaterThan(2);

        await pickupPoints.nth(0).locator('a').click();
        await pickupPoints.nth(1).locator('a').click();

        const thirdPickup = pickupPoints.nth(2);
        const thirdPickupName = await thirdPickup.locator('a').innerText();
        await thirdPickup.locator('a').click();

        const popupTitle = page.locator('#pickup-points-map .marker-popup__title').first();
        await expect(popupTitle).toBeVisible();
        await expect(popupTitle).toContainText(thirdPickupName);

        await page.locator('button.pickup-point__select-btn').click();
        await expect(page.locator('#pickup-points-map-modal')).toBeHidden();

        const selectedPickup = page.locator('.selected-pickup-point').first();
        await expect(selectedPickup).toContainText(thirdPickupName);

        await page.getByRole('button', { name: 'Place Order' }).click();

        const confirmationAddress = page.locator('.woocommerce-column--shipping-address address');
        await expect(confirmationAddress).toContainText(thirdPickupName);
    });

    test('Saves custom business order address fields', async ({ page }) => {
        await page.goto('/checkout');

        await page.waitForLoadState('networkidle');
        const emailInput = page.locator('.wc-block-components-address-form__email input');
        await expect(emailInput).toBeVisible();
        await expect(emailInput).toHaveValue('');
        await emailInput.fill('test@test.com');


        const inputValues = {
            'shipping-first_name': 'Jean',
            'shipping-last_name': 'Némar',
            'shipping-address_1': '1 rue du Test Automatisé',
            'shipping-postcode': '45000',
            'shipping-city': 'Orléans',
            'shipping-hges-is-company-address': true,
            'shipping-hges-company-name': 'Test Company',
            'shipping-hges-excise-number': '12345678901234',
        };
        await formFillById(page, inputValues);
        const shippingAddressFieldset = page.locator('.wp-block-woocommerce-checkout-shipping-methods-block');
        await selectRateInAccordion(page, shippingAddressFieldset, 'Door Delivery', 'Chrono 13h');
        const placeOrderBtn = page.locator('button.wc-block-components-checkout-place-order-button');
        await expect(placeOrderBtn).toBeVisible();
        await placeOrderBtn.click();
        await expect(page).toHaveURL(/\/order-received/);
        await checkOrderConfirmationContent(page, false);
    });
});