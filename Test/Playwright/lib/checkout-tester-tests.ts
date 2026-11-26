import {
    test,
    expect,
    configure,
    getSignedOrderParams,
    placeGuestOrders,
    openSuccessPage,
    expectSuccessPage,
    expectBlocked,
    isDeveloperMode,
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

    test('falls back to the latest order when the URL has no token', async function ({page}) {
        await configure(page, theme);
        const response = await openSuccessPage(page, {order_id: firstOrder.entityId});
        await expectSuccessPage(page, response, latestOrder.incrementId);
        await expect(page.locator('body')).not.toContainText(firstOrder.incrementId);
    });

    test.describe('signed order links', function () {
        test('shows the order given in the URL with a valid token', async function ({page}) {
            const params = await getSignedOrderParams(page, theme, firstOrder);
            const response = await openSuccessPage(page, params);
            await expectSuccessPage(page, response, firstOrder.incrementId);
            await expect(page.locator('body')).not.toContainText(latestOrder.incrementId);
        });

        test('falls back to the latest order with a tampered token', async function ({page}) {
            const params = await getSignedOrderParams(page, theme, firstOrder);
            const token = (params.token[0] === '0' ? '1' : '0') + params.token.slice(1);
            const response = await openSuccessPage(page, {...params, token});
            await expectSuccessPage(page, response, latestOrder.incrementId);
            await expect(page.locator('body')).not.toContainText(firstOrder.incrementId);
        });

        test('falls back to the latest order for a URL with a past expiry', async function ({page}) {
            const params = await getSignedOrderParams(page, theme, firstOrder);
            const expires = String(Math.floor(Date.now() / 1000) - 60);
            const response = await openSuccessPage(page, {...params, expires});
            await expectSuccessPage(page, response, latestOrder.incrementId);
            await expect(page.locator('body')).not.toContainText(firstOrder.incrementId);
        });
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

    test('only honors a wildcard allowlist in developer mode', async function ({page}) {
        await configure(page, theme, {'checkouttester2/settings/ip': '*'});
        try {
            const response = await openSuccessPage(page);
            if (isDeveloperMode()) {
                await expectSuccessPage(page, response, latestOrder.incrementId);
            } else {
                await expectBlocked(page, response);
            }
        } finally {
            await configure(page, theme);
        }
    });

    test('blocks access for an IP address that is not whitelisted', async function ({page}) {
        await configure(page, theme, {'checkouttester2/settings/ip': '10.0.0.1, 1.1.1.1'});
        const response = await openSuccessPage(page);
        await expectBlocked(page, response);
    });

    test('blocks access with a spoofed Client-IP or X-Forwarded-For header', async function ({page}) {
        await configure(page, theme, {'checkouttester2/settings/ip': '1.1.1.1'});
        const response = await openSuccessPage(page, {}, {'Client-IP': '1.1.1.1', 'X-Forwarded-For': '1.1.1.1'});
        await expectBlocked(page, response);
    });
}
