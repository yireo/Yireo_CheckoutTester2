import {test, LUMA_THEME} from './lib/checkout-tester';
import {defineCheckoutTesterTests} from './lib/checkout-tester-tests';

test.use({trace: 'off', video: 'off', screenshot: 'off'});

test.describe('CheckoutTester success page (Luma)', function () {
    defineCheckoutTesterTests(LUMA_THEME);
});
