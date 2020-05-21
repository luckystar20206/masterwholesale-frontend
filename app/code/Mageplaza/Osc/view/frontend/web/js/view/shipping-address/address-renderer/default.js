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

define([
    'Magento_Checkout/js/view/shipping-address/address-renderer/default',
    'Magento_Checkout/js/model/shipping-rate-service',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/quote'
], function (Component, shippingRateService, rateRegistry, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageplaza_Osc/container/address/shipping/address-renderer/default'
        },

        /** Set selected customer shipping address  */
        selectAddress: function () {
            if (!this.isSelected()) {
                this._super();

                if (quote.shippingAddress().getType == 'customer-address') {
                    rateRegistry.set(quote.shippingAddress().getKey(), null);
                } else {
                    rateRegistry.set(quote.shippingAddress().getCacheKey(), null);
                }

                shippingRateService.isAddressChange = true;
                shippingRateService.estimateShippingMethod();
            }
        }
    });
});
