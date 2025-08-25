import { expect } from "@playwright/test";

export const selectRateInAccordion = async (page, parentLocator, headerTitle, method) => {
    let methodName = method;
   
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

    let methodNameContainer;    
    if (typeof method === 'number') {

        const rateElements = await header.locator('xpath=ancestor::div[contains(@class, "accordion")]//p[contains(@class,"rate-name")]').all();
        if (rateElements.length <= method) {
            throw new Error('Method index out of bounds');
        }

        if (rateElements[method] === undefined) {
            throw new Error('No rate element found at index ' + method);
        }
        methodNameContainer = rateElements[method];
    } else {
        methodNameContainer = await page.locator('.shipping-rates .rate-name span', { hasText: methodName }).first();
    }

    await methodNameContainer.waitFor({ state: 'visible' });

    const labelContent = methodNameContainer.locator('xpath=ancestor::label//div[contains(@class,"rate-content")]');
    await labelContent.waitFor({ state: 'visible' });
    await labelContent.click();

    return expect(labelContent).toHaveClass(/selected/);
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