import { expect } from '@playwright/test';

export async function formFillById(page, formData) {
    for (const [inputId, value] of Object.entries(formData)) {
        const escapedId = cssEscape(inputId);
        const inputLocator = page.locator(`input#${escapedId}`);
        await expect(inputLocator).toBeVisible();
        await expect(inputLocator).toBeEnabled();  
        const type = await inputLocator.getAttribute('type');
        switch (type) {
            case 'checkbox':
                if (value) {
                    await inputLocator.check({force: true});
                } else {
                    await inputLocator.uncheck();
                }
                await expect(inputLocator).toBeChecked(value);
                break;
            default:
                await inputLocator.click({force: true});
                await inputLocator.type(String(value));
                await expect(await inputLocator.inputValue()).toBe(String(value));
                break;
        }   
    }
    return page.waitForLoadState('networkidle');
}

function cssEscape(str) {
  return str.replace(/([!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~])/g, '\\$1');
}