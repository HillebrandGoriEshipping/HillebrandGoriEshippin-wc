import { expect } from '@playwright/test';

export async function formFillById(page, formData) {
    for (const [inputId, value] of Object.entries(formData)) {
        const inputLocator = page.locator(`input#${inputId}`);
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
                await inputLocator.fill(String(value));
                await expect(await inputLocator.inputValue()).toBe(String(value));
                break;
        }   
    }
    return page.waitForLoadState('networkidle');
}

