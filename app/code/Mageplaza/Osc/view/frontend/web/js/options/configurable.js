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
    'jquery',
    'Magento_ConfigurableProduct/js/configurable',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.mposcConfigurable', $.mage.configurable, {
        _initializeOptions: function () {
            var element = $(this.options.priceHolderSelector);

            if (!element.data('magePriceBox')) {
                element.priceBox();
            }

            return this._super();
        },

        _calculatePrice: function (config) {
            var element = $(this.options.priceHolderSelector);

            if (!element.data('magePriceBox')) {
                element.priceBox();
            }

            return this._super(config);
        },
    });

    return $.mage.mposcConfigurable;
});
