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
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data'
    ], function ($,
                 _,
                 Component,
                 quote,
                 customer,
                 checkoutData) {
        'use strict';

        var isEnableGeoIp = window.checkoutConfig.oscConfig.geoIpOptions.isEnableGeoIp,
            geoIpData = window.checkoutConfig.oscConfig.geoIpOptions.geoIpData;

        return Component.extend({
            initialize: function () {
                this.initGeoIp();
                this._super();
                return this;
            },
            initGeoIp: function () {
                if (isEnableGeoIp) {
                    if (!quote.isVirtual()) { /** Set Geo Ip data to shippingAddress */
                        if ((!customer.isLoggedIn() && checkoutData.getShippingAddressFromData() == null)
                            || (customer.isLoggedIn() && checkoutData.getNewCustomerShippingAddress() == null)) {
                            checkoutData.setShippingAddressFromData(geoIpData);
                        }
                    } else { /** Set Geo Ip data to billingAddress */
                        if ((!customer.isLoggedIn() && checkoutData.getBillingAddressFromData() == null)
                            || (customer.isLoggedIn() && checkoutData.getNewCustomerBillingAddress() == null)) {
                            checkoutData.setBillingAddressFromData(geoIpData);
                        }
                    }
                }
            }
        });
    }
);
