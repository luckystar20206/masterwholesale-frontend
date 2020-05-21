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

/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Mageplaza_Osc/js/model/osc-data'
    ],
    function (ko, Component, quote, totals, oscData) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/container/summary/gift-wrap'
            },
            totals: quote.getTotals(),
            isDisplay: function () {
                return this.getPureValue() >= 0 && oscData.getData('is_use_gift_wrap');
            },
            getPureValue: function () {
                var giftWrapAmount = 0;

                if (this.totals() && totals.getSegment('osc_gift_wrap')) {
                    giftWrapAmount = parseFloat(totals.getSegment('osc_gift_wrap').value);
                }

                return giftWrapAmount;
            },
            getValue: function () {
                return this.getFormattedPrice(this.getPureValue());
            }
        });
    }
);
