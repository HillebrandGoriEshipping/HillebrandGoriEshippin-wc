import { expect } from '@playwright/test';

export async function checkFieldValidation(page, fieldSelector, incorrectValue, correctValue, expectedErrorMessage) {
    // first, try with an incorrect value
    await page.fill(fieldSelector, incorrectValue);
    await page.locator(fieldSelector).locator('xpath=ancestor::form').evaluate(form => form.submit());

    // the error message should be visible
    const errorLocator = page.locator(fieldSelector).locator('xpath=ancestor::td').locator('.error-message');
    await expect(errorLocator).toBeVisible();
    await expect(errorLocator).toHaveText(expectedErrorMessage);

    // now, fill the field with the correct value
    await page.focus(fieldSelector);
    await page.fill(fieldSelector, correctValue);
    await page.locator(fieldSelector).locator('xpath=ancestor::form').evaluate(form => form.submit());

    // the error message should be hidden
    await expect(errorLocator).toBeHidden();

    // and the field should have the correct value
    await expect(page.locator(fieldSelector)).toHaveValue(correctValue);
}