import { expect } from '@playwright/test';

export async function formFillById(page, formData) {

    for (const [inputId, value] of Object.entries(formData)) {
        const inputLocator = page.locator(`input#${inputId}`);
        await expect(inputLocator).toBeVisible();

        const type = await inputLocator.getAttribute('type');
        switch (type) {
            case 'checkbox':
                if (value) {
                    await inputLocator.check();
                } else {
                    await inputLocator.uncheck();
                }
                break;
            default:
                await inputLocator.fill(String(value));
                break;
        }

        await expect(await inputLocator.inputValue()).toBe(String(value));
    }
}

