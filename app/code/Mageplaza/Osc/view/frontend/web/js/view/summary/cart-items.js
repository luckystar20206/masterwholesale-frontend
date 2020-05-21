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
    'ko',
    'underscore',
    'jquery',
    'Magento_Checkout/js/view/summary/cart-items',
    'Mageplaza_Osc/js/model/osc-data'
], function (ko, _, $, Component, oscData) {
    "use strict";

    var cacheKey = 'is_cart_expanded';

    return Component.extend({
        toggleCart: function () {
            oscData.setData(cacheKey, !this.isCartExpanded());
        },

        isCartExpanded: function () {
            var isExpanded = oscData.getData(cacheKey);

            return typeof isExpanded === 'undefined' ? true : isExpanded;
        }
    });
});
