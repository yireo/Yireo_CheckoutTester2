import {
    test,
    expect,
    configure,
    placeGuestOrders,
    openSuccessPage,
    expectSuccessPage,
    expectBlocked,
    HYVA_THEME,
    PlacedOrder,
} from './checkout-tester';

/**
 * Shared test cases for Luma and Hyva, so both themes are covered with the very same assertions
 */
export function defineCheckoutTesterTests(theme: string) {
    let firstOrder: PlacedOrder;
    let latestOrder: PlacedOrder;

    test.beforeAll(async function ({browser}) {
        [firstOrder, latestOrder] = await placeGuestOrders(browser, theme);
    });

    test('shows the latest order on the success page', async function ({page}) {
        await configure(page, theme);
        const response = await openSuccessPage(page);
        await expectSuccessPage(page, response, latestOrder.incrementId);
    });

    test('shows the order given in the URL', async function ({page}) {
        await configure(page, theme);
        const response = await openSuccessPage(page, {order_id: firstOrder.entityId});
        await expectSuccessPage(page, response, firstOrder.incrementId);
        await expect(page.locator('body')).not.toContainText(latestOrder.incrementId);
    });

    test('shows the order given in the configuration', async function ({page}) {
        await configure(page, theme, {'checkouttester2/settings/order_id': firstOrder.entityId});
        const response = await openSuccessPage(page);
        await expectSuccessPage(page, response, firstOrder.incrementId);
    });

    test('falls back to the latest order with an unknown order in the URL', async function ({page}) {
        await configure(page, theme);
        const response = await openSuccessPage(page, {order_id: '999999999'});
        await expectSuccessPage(page, response, latestOrder.incrementId);
    });

    test('renders the success page with its own title and robots meta', async function ({page}) {
        await configure(page, theme);
        await openSuccessPage(page);

        await expect(page).toHaveTitle(/CheckoutTester Success Page/);
        await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', 'NOINDEX,NOFOLLOW');
        await expect(page.locator('body')).toHaveClass(/checkout-onepage-success/);
    });

    test('renders the success page in the configured theme', async function ({page}) {
        await configure(page, theme);
        await openSuccessPage(page);

        const usesRequireJs = await page.evaluate(() => typeof (window as any).require === 'function');
        const usesHyva = await page.evaluate(() => typeof (window as any).hyva === 'object');
        expect(usesHyva, 'Hyva is loaded').toBe(theme === HYVA_THEME);
        expect(usesRequireJs, 'RequireJS is loaded').toBe(theme !== HYVA_THEME);
    });

    test('blocks the success page when disabled', async function ({page}) {
        await configure(page, theme, {'checkouttester2/settings/enabled': 0});
        const response = await openSuccessPage(page);
        await expectBlocked(page, response);
    });

    test('allows access for a whitelisted IP address', async function ({page}) {
        await configure(page, theme, {'checkouttester2/settings/ip': '10.0.0.1, 1.1.1.1'});
        const response = await openSuccessPage(page, {}, {'Client-IP': '1.1.1.1'});
        await expectSuccessPage(page, response, latestOrder.incrementId);
    });

    test('blocks access for an IP address that is not whitelisted', async function ({page}) {
        await configure(page, theme, {'checkouttester2/settings/ip': '10.0.0.1, 1.1.1.1'});
        const response = await openSuccessPage(page, {}, {'Client-IP': '1.1.1.2'});
        await expectBlocked(page, response);
    });
}
