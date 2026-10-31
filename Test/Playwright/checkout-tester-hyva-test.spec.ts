import {test, HYVA_THEME} from './lib/checkout-tester';
import {defineCheckoutTesterTests} from './lib/checkout-tester-tests';

test.describe('CheckoutTester success page (Hyva)', function () {
    defineCheckoutTesterTests(HYVA_THEME);
});
