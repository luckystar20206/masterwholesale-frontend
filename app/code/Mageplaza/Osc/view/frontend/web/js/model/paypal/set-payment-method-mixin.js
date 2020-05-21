/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Mageplaza_Osc/js/action/set-payment-method'
], function ($, wrapper, setPaymentMethodAction) {
    'use strict';

    return function (originalSetPaymentMethodAction) {
        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(originalSetPaymentMethodAction, function (originalAction, messageContainer) {
            return setPaymentMethodAction(messageContainer);
        });
    };
});
