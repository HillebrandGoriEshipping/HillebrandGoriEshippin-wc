import { expect } from "@playwright/test";

export const selectRateInAccordion = async (page, parentLocator, headerTitle, method) => {
    console.log('headerTitle', headerTitle);
    let methodName = method;
    if (typeof method === 'number') {
        const rateElements = await page.$$('.shipping-rates .rate-name span');
        if (rateElements.length <= method) {
            throw new Error('Method index out of bounds');
        }
        methodName = (await rateElements[method].textContent()).trim();
        if (!methodName) {
            throw new Error('Shipping method name is empty');
        }
    }

    const loadingMask = page.locator('.wc-block-components-totals-shipping__fieldset .wc-block-components-loading-mask');
    await loadingMask.waitFor({ state: 'hidden' });

    const header = await parentLocator.locator('span', { hasText: headerTitle }).locator('xpath=ancestor::button[contains(@class,"accordion-header")]');
    await header.waitFor({ state: 'visible' });
    const headerCount = await header.count();
    expect(headerCount).toBeGreaterThan(0);

    const classList = await header.getAttribute('class');
    if (classList.includes('collapsed')) {
        await header.click();
    }

    const label = await page.locator('.shipping-rates .rate-name span', { hasText: methodName }).first();
    await label.waitFor({ state: 'visible' });

    const rate = label.locator('xpath=ancestor::*[contains(@class, "rate-content")]');
    await rate.waitFor({ state: 'visible' });
    await rate.click();
    return expect(rate).toHaveClass(/selected/);
};

export const selectRateByName = async (page, labelText) => {
 
    const labelDiv = page.locator('.hges-shipping-label', { hasText: labelText }).first();
    await expect(labelDiv).toBeVisible();
    const label = labelDiv.locator('xpath=ancestor::label');
    await expect(label).toBeVisible();
    await label.click();

    const forAttr = await label.getAttribute('for');
    const input = page.locator(`#${forAttr}`);
    return expect(input).toBeChecked();
};