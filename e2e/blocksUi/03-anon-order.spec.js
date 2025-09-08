
import { test } from '../helpers/fixtures';
import { expect } from '@playwright/test';
import setUiToBlocks from '../../scripts/setUiToBlocks';
import { selectRateInAccordion } from '../helpers/shippingRates';
import { checkOrderConfirmationContent } from '../helpers/orderConfirmation';
import { addToCart } from '../helpers/cart';
import { formFillById } from '../helpers/formFill';
import adminLogin from '../helpers/adminLogin';

let createdOrderId = null;

test.describe('Block UI Order spec', () => {
    test.beforeAll(async () => {
        await setUiToBlocks();
    });

    test.beforeEach(async ({ page }) => {
        await addToCart(page, 'blocks');
    });

    test('Select a shipping method in checkout view', async ({ page, customer }) => {
        await page.goto('/checkout');

        const emailInput = page.locator('.wc-block-components-address-form__email input');
        await expect(emailInput).toBeVisible();
        await expect(emailInput).toHaveValue('');
        await emailInput.fill('test@test.com');
        const inputValues = {
            'shipping-first_name': customer().firstName,
            'shipping-last_name': customer().lastName,
            'shipping-address_1': customer().address1,
            'shipping-postcode': customer().postcode,
            'shipping-city': customer().city
        };
        formFillById(page, inputValues);

        const shippingAddressFieldset = page.locator('.wp-block-woocommerce-checkout-shipping-methods-block');
        await selectRateInAccordion(page, shippingAddressFieldset, 'Door Delivery', 0);

        const placeOrderBtn = page.locator('button.wc-block-components-checkout-place-order-button');
        await expect(placeOrderBtn).toBeVisible();
        await placeOrderBtn.click();
        await checkOrderConfirmationContent(page, false);
    });

    test('Selects a pickup point in map', async ({ page, customer }) => {
        await page.goto('/cart');
        await page.getByText('Proceed to Checkout').click();

        const emailInput = page.locator('.wc-block-components-address-form__email input');
        await expect(emailInput).toBeVisible();
        await expect(emailInput).toHaveValue('');
        await emailInput.fill('test@test.com');

        const inputValues = {
            'shipping-first_name': customer().firstName,
            'shipping-last_name': customer().lastName,
            'shipping-address_1': customer().address1,
            'shipping-postcode': customer().postcode,
            'shipping-city': customer().city
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

    test('Saves custom business order address fields', async ({ page, customer }) => {
        await page.goto('/checkout');

        await page.waitForLoadState('networkidle');
        const emailInput = page.locator('.wc-block-components-address-form__email input');
        await expect(emailInput).toBeVisible();
        await expect(emailInput).toHaveValue('');
        await emailInput.fill('test@test.com');


        const inputValues = {
            'shipping-first_name': customer().firstName,
            'shipping-last_name': customer().lastName,
            'shipping-address_1': customer().address1,
            'shipping-postcode': customer().postcode,
            'shipping-city': customer().city,
            'shipping-hges-is-company-address': customer(true).isCompanyAddress,
            'shipping-hges-company-name': customer(true).companyName,
            'shipping-hges-excise-number': customer(true).exciseNumber,
        };
        
        await formFillById(page, inputValues);
        const shippingAddressFieldset = page.locator('.wp-block-woocommerce-checkout-shipping-methods-block');
        await page.waitForTimeout(5000);  
        await selectRateInAccordion(page, shippingAddressFieldset, 'Door Delivery', 0);
        const placeOrderBtn = page.locator('button.wc-block-components-checkout-place-order-button');
        await expect(placeOrderBtn).toBeVisible();
        await placeOrderBtn.click();
        await expect(page).toHaveURL(/\/order-received/);
        await checkOrderConfirmationContent(page, false);

        createdOrderId = page.url().match(/order-received\/(\d+)\//)[1];
    });

});

test.describe('Order admin view spec', () => {
    let orderPageUrl = null;
    test('Display order admin page', async ({ page }) => {
        await page.goto('/wp/wp-admin/admin.php?page=wc-orders');
        await adminLogin(page);
        const orderLink = page.locator(`a[href*="page=wc-orders&action=edit&id=${createdOrderId}"]`).first();
        await expect(orderLink).toBeVisible();
        await orderLink.click();
        await expect(page).toHaveURL(new RegExp(`page=wc-orders&action=edit&id=${createdOrderId}`));
        orderPageUrl = page.url();
    });

    test('Save order', async ({ page }) => {
        await page.goto(orderPageUrl);
        await adminLogin(page);

        const statusSelect = page.locator('#order_status');
        await statusSelect.selectOption({ label: 'Processing' });

        const updateButton = page.locator('.order_actions button[type="submit"] ');
        await expect(updateButton).toBeVisible();
        await updateButton.click();

        await expect(page.locator('#message.updated.notice-success')).toBeVisible();
    });
});