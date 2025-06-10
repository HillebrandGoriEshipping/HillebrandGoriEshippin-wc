import { expect } from "@playwright/test";

export const selectRateInAccordion = async (page, headerTitle, method) => {
    let methodName = method;
    if (typeof method === 'number') {
        const rateElements = await page.$$('.shipping-rates .rate-name span');
        if (rateElements.length <= method) {
            throw new Error('Method index out of bounds');
        }
        methodName = (await rateElements[method].innerText()).trim();
        if (!methodName) {
            throw new Error('Shipping method name is empty');
        }
    }

    await page.waitForSelector('.wc-block-components-totals-shipping__fieldset .wc-block-components-loading-mask', { state: 'detached' });

    const header = await page.locator('span', { hasText: headerTitle }).locator('xpath=ancestor::button[contains(@class,"accordion-header")]');
    await header.waitFor({ state: 'visible' });

    const classList = await header.getAttribute('class');
    if (classList.includes('collapsed')) {
        await header.click();
    }

    const label = await page.locator('.shipping-rates .rate-name span', { hasText: methodName }).first();
    await label.waitFor({ state: 'visible' });

    const rate = await label.evaluateHandle(el => el.closest('.rate-content'));
    await rate.asElement().click();
    await page.waitForTimeout(10000);
    const selectedClass = await rate.asElement().getAttribute('class');
    expect(selectedClass).toContain('selected');
};