import {test, expect, Browser, Page, Response} from '@playwright/test';
import {createConnection, isIP} from 'node:net';

export {test, expect};

export const HYVA_THEME = 'Hyva/default-csp';
export const LUMA_THEME = 'Magento/luma';

export const SUCCESS_PATH = '/checkouttester/index/success';
export const THANK_YOU = 'Thank you for your purchase!';

const SKU = 'checkouttester2-product';

const address = {
    firstname: 'John',
    lastname: 'Doe',
    street: ['Main Street 1'],
    city: 'Amsterdam',
    postcode: '1000AA',
    country_id: 'NL',
    telephone: '0123456789',
    email: 'john.doe@example.com',
};

export type PlacedOrder = {
    entityId: string;
    incrementId: string;
};

const token = () => process.env.TEST_TOKEN;

async function clientIp(): Promise<string> {
    const configuredIp = process.env.CHECKOUTTESTER_CLIENT_IP;
    if (configuredIp) {
        expect(isIP(configuredIp), 'CHECKOUTTESTER_CLIENT_IP must be a single IP address').not.toBe(0);
        return configuredIp;
    }

    const url = new URL(process.env.TEST_URL!);
    return new Promise((resolve, reject) => {
        const socket = createConnection({
            host: url.hostname.replace(/^\[|\]$/g, ''),
            port: Number(url.port || (url.protocol === 'https:' ? 443 : 80)),
        });
        socket.once('error', reject);
        socket.setTimeout(5000, () => socket.destroy(new Error('Could not determine the Playwright client IP')));
        socket.once('connect', () => {
            const address = socket.localAddress;
            socket.destroy();
            if (address) {
                resolve(address);
            } else {
                reject(new Error('The Magento connection has no local IP address'));
            }
        });
    });
}

export async function configure(page: Page, theme: string, settings: Record<string, string | number> = {}) {
    const response = await page.request.post('/loki_functional_tests/index/configure?token=' + token(), {
        form: {
            config: JSON.stringify({
                theme,
                product: {sku: SKU, name: 'CheckoutTester2 Product', price: 10, tax_class_id: 0},
                config: {
                    'carriers/flatrate/active': 1,
                    'payment/checkmo/active': 1,
                    'checkouttester2/settings/enabled': 1,
                    'checkouttester2/settings/only_in_dev_mode': 0,
                    'checkouttester2/settings/order_id': 0,
                    'checkouttester2/settings/ip': settings['checkouttester2/settings/ip'] ?? await clientIp(),
                    'checkouttester2/settings/trusted_proxies': '',
                    ...settings,
                },
            }),
        },
    });

    const body = await response.text();
    let data: any;
    try {
        data = JSON.parse(body);
    } catch (error) {
        throw new Error(`Configure endpoint returned no JSON (status ${response.status()}): ${body.slice(0, 500)}`);
    }

    if (data.error) {
        throw new Error('Configure error: ' + data.error);
    }

    const themeErrors: string[] = data.result?.theme?.errors ?? [];
    test.skip(themeErrors.some(error => error.includes('not found')), `Theme "${theme}" is not installed`);
    expect(themeErrors, 'Configure theme errors').toEqual([]);
}

async function followAdminLink(page: Page, link: ReturnType<Page['locator']>) {
    const href = await link.getAttribute('href', {timeout: 10000});
    expect(href, 'Admin navigation link').toBeTruthy();
    await page.goto(new URL(href!, page.url()).href, {timeout: 15000});
}

export async function getSignedOrderParams(page: Page, theme: string, order: PlacedOrder) {
    await configure(page, theme, {'checkouttester2/settings/order_id': order.entityId});

    try {
        await page.goto('/' + (process.env.MAGENTO_BACKEND_FRONTNAME || 'admin'), {timeout: 15000});
        await page.locator('#username').fill(process.env.MAGENTO_ADMIN_USER || 'admin@loki-checkout.com', {timeout: 10000});
        await page.locator('#login').fill(process.env.MAGENTO_ADMIN_PASSWORD || 'P@ssw0rd1234', {timeout: 10000});
        await page.getByRole('button', {name: 'Sign in', exact: true}).click({timeout: 10000});
        await followAdminLink(page, page.locator('#menu-magento-backend-stores a').filter({hasText: /^Configuration$/}));
        await followAdminLink(page, page.locator('.config-nav a').filter({hasText: /^\s*Yireo CheckoutTester\s*$/}));

        const href = await page.getByRole('link', {name: 'Open success page in new window'})
            .getAttribute('href', {timeout: 10000});
        expect(href, 'Admin-generated success link').toBeTruthy();
        const params = new URL(href!, page.url()).searchParams;
        const orderId = params.get('order_id');
        const expires = params.get('expires');
        const signature = params.get('token');
        expect(orderId).toBe(order.entityId);
        expect(Number(expires)).toBeGreaterThan(Date.now() / 1000);
        expect(signature).toBeTruthy();

        return {order_id: orderId!, expires: expires!, token: signature!};
    } finally {
        await configure(page, theme);
    }
}

async function rest(page: Page, path: string, data?: Record<string, any>) {
    const response = await page.request.post('/rest/default/V1/' + path, {data: data ?? {}});
    const body = await response.text();
    expect(response.ok(), `REST ${path} failed: ${body.slice(0, 500)}`).toBeTruthy();

    return JSON.parse(body);
}

/**
 * Place a guest order (flat rate, check / money order) through the guest REST API, and read its increment ID
 * from the regular success page, because QuoteManagement::placeOrder() stores the order in the checkout session
 */
export async function placeGuestOrder(page: Page): Promise<PlacedOrder> {
    await page.goto('/');

    const cartId = await rest(page, 'guest-carts');
    await rest(page, `guest-carts/${cartId}/items`, {
        cartItem: {sku: SKU, qty: 1, quote_id: cartId},
    });
    await rest(page, `guest-carts/${cartId}/shipping-information`, {
        addressInformation: {
            shipping_address: address,
            billing_address: address,
            shipping_carrier_code: 'flatrate',
            shipping_method_code: 'flatrate',
        },
    });
    const entityId = await rest(page, `guest-carts/${cartId}/payment-information`, {
        email: address.email,
        paymentMethod: {method: 'checkmo'},
        billingAddress: address,
    });

    await page.goto('/checkout/onepage/success/');
    const incrementId = (await page.locator('.checkout-success').innerText()).match(/\d{6,}/)?.[0];
    expect(incrementId, 'Increment ID on the regular success page').toBeTruthy();

    return {entityId: String(entityId), incrementId: incrementId!};
}

/**
 * Place two orders with a fresh browser context, so that the checkout session of the tests stays empty
 */
export async function placeGuestOrders(browser: Browser, theme: string): Promise<PlacedOrder[]> {
    const context = await browser.newContext({baseURL: process.env.TEST_URL, ignoreHTTPSErrors: true});
    const page = await context.newPage();

    try {
        await configure(page, theme);

        return [await placeGuestOrder(page), await placeGuestOrder(page)];
    } finally {
        await context.close();
    }
}

/**
 * Open the CheckoutTester success page, bypassing the full page cache
 */
export async function openSuccessPage(
    page: Page,
    params: Record<string, string> = {},
    headers: Record<string, string> = {}
): Promise<Response> {
    const query = new URLSearchParams({...params, checkouttester2: String(Date.now())});
    if (Object.keys(headers).length > 0) {
        await page.setExtraHTTPHeaders(headers);
    }

    const response = await page.goto(SUCCESS_PATH + '?' + query.toString());
    expect(response, 'Success page response').not.toBeNull();

    return response!;
}

export async function expectSuccessPage(page: Page, response: Response, incrementId: string) {
    expect(response.status(), 'Success page status').toBe(200);
    await expect(page.locator('body')).toContainText(THANK_YOU);
    await expect(page.locator('body')).toContainText(incrementId);
}

export async function expectBlocked(page: Page, response: Response) {
    expect(response.status(), 'Blocked page status').toBeGreaterThanOrEqual(400);
    await expect(page.locator('body')).not.toContainText(THANK_YOU);
}
