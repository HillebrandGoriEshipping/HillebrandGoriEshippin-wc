export default async function adminLogin(page) {
    await page.locator('#user_login').fill('hges');
    const passwordInput = page.locator('#user_pass');
    await passwordInput.waitFor();
    await passwordInput.fill('hges');
    await page.waitForTimeout(10000);
    return page.locator('#wp-submit').click();
}