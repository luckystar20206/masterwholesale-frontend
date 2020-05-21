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
    'Mageplaza_Osc/js/action/set-checkout-information',
    'Mageplaza_Osc/js/model/braintree-paypal',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/quote',
    'underscore',
    'uiRegistry'
], function ($, setCheckoutInformationAction, braintreePaypalModel, additionalValidators, quote, _, registry) {
    'use strict';
    return function (BraintreePaypalComponent) {
        return BraintreePaypalComponent.extend({
            defaults: {
                template: 'Mageplaza_Osc/payment/paypal_map',

                clientConfig: {
                    buttonId: 'osc_braintree_paypal_placeholder',
                }
            },
            /**
             * Set list of observable attributes
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                var self = this;

                this._super();
                // For each component initialization need update property
                this.isReviewRequired = braintreePaypalModel.isReviewRequired;
                this.customerEmail = braintreePaypalModel.customerEmail;
                this.active = braintreePaypalModel.active;

                return this;
            },

            /**
             * Get shipping address
             * @returns {Object}
             */
            getShippingAddress: function () {
                var address = quote.shippingAddress(),
                    directoryCountryData = window.checkoutConfig.oscConfig.directoryData;
                if (!address) {
                    address = {};
                }
                if (!address.street) {
                    address.street = ['', ''];
                }
                if (address.postcode === null) {
                    return {};
                }

                var shippingState = typeof address.countryId !== 'undefined' ? directoryCountryData[address.countryId].regions[address.regionId].code : null;
                return  {
                    recipientName: address.firstname + ' ' + address.lastname,
                    line1: typeof address.street !== 'undefined'  ? address.street[0] : '',
                    line2: typeof address.street !== 'undefined'? (typeof address.street[2] === 'undefined' ? address.street[1] : address.street[1] + ' ' + address.street[2]) : '',
                    city: address.city,
                    countryCode: address.countryId,
                    postalCode: address.postcode,
                    state: (shippingState ? shippingState : '')
                };
            },

            // Compatible with PayPal Through Braintree on M231
            reInitPayPal: function () {
                var placeOrder = registry.get('checkout.sidebar.place-order-information-right.place-order-button');
                if (!placeOrder.isPaypalThroughBraintree) {
                    this._super();
                }
            }
        })
    }
});