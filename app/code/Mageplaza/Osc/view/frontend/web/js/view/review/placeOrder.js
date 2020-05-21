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
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'underscore',
        'ko',
        'uiComponent',
        'uiRegistry',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data',
        'Mageplaza_Osc/js/action/set-checkout-information',
        'Mageplaza_Osc/js/model/braintree-paypal'
    ],
    function ($,
              _,
              ko,
              Component,
              registry,
              quote,
              additionalValidators,
              customerData,
              setCheckoutInformationAction,
              braintreePaypalModel) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/container/review/place-order',
                visibleBraintreeButton: false,
            },
            braintreePaypalModel: braintreePaypalModel,
            selectors: {
                default: '#co-payment-form .payment-method._active button.action.primary.checkout'
            },
            isPaypalThroughBraintree: false,
            initialize: function () {
                this._super();
                var self = this;
                quote.paymentMethod.subscribe(function (value) {
                    self.processVisiblePlaceOrderButton();
                });

                registry.async(this.getPaymentPath('braintree_paypal'))
                (this.asyncBraintreePaypal.bind(this));

                return this;
            },
            /**
             * Set list of observable attributes
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                var self = this;

                this._super()
                    .observe(['visibleBraintreeButton']);

                return this;
            },
            asyncBraintreePaypal: function () {
                this.processVisiblePlaceOrderButton();
            },
            isBraintreeNewVersion: function () {
                var component = this.getBraintreePaypalComponent();
                return component
                    && typeof component.isReviewRequired == "function"
                    && typeof component.getButtonTitle == "function";
            },
            processVisiblePlaceOrderButton: function () {
                this.visibleBraintreeButton(this.checkVisiblePlaceOrderButton());
            },
            checkVisiblePlaceOrderButton: function () {
                return this.getBraintreePaypalComponent()
                    && this.isPaymentBraintreePaypal();
            },
            placeOrder: function () {
                var self = this;
                if (additionalValidators.validate()) {
                    this.preparePlaceOrder().done(function () {
                        self._placeOrder();
                    });
                } else {
                    var offsetHeight = $(window).height() / 2,
                        errorMsgSelector = $('#maincontent .mage-error:visible:first').closest('.field');
                    errorMsgSelector = errorMsgSelector.length ? errorMsgSelector : $('#maincontent .field-error:visible:first').closest('.field');
                    if (errorMsgSelector.length) {
                        if (errorMsgSelector.find('select').length) {
                            $('html, body').scrollTop(
                                errorMsgSelector.find('select').offset().top - offsetHeight
                            );
                            errorMsgSelector.find('select').focus();
                        } else if (errorMsgSelector.find('input').length) {
                            $('html, body').scrollTop(
                                errorMsgSelector.find('input').offset().top - offsetHeight
                            );
                            errorMsgSelector.find('input').focus();
                        }
                    } else if ($('.message-error:visible').length) {
                        $('html, body').scrollTop(
                            $('.message-error:visible:first').closest('div').offset().top - offsetHeight
                        );
                    }
                }

                return this;
            },

            brainTreePaypalPlaceOrder: function () {
                var component = this.getBraintreePaypalComponent();
                if (component && additionalValidators.validate()) {
                    component.placeOrder.apply(component, arguments);
                }

                return this;
            },

            brainTreePayWithPayPal: function () {
                var self = this;
                var component = this.getBraintreePaypalComponent();
                self.isPaypalThroughBraintree = true;
                if (component && additionalValidators.validate()) {
                    component.payWithPayPal.apply(component, arguments);
                }

                return this;
            },
            preparePlaceOrder: function (scrollTop) {
                var scrollTop = scrollTop !== undefined ? scrollTop : true;
                var deferer = $.when(setCheckoutInformationAction());

                return scrollTop ? deferer.done(function () {
                    $("body").animate({scrollTop: 0}, "slow");
                }) : deferer;
            },

            getPaymentPath: function (paymentMethodCode) {
                return 'checkout.steps.billing-step.payment.payments-list.' + paymentMethodCode;
            },

            getPaymentMethodComponent: function (paymentMethodCode) {
                return registry.get(this.getPaymentPath(paymentMethodCode));
            },

            isPaymentBraintreePaypal: function () {
                return quote.paymentMethod() && quote.paymentMethod().method === 'braintree_paypal';
            },

            getBraintreePaypalComponent: function () {
                return this.getPaymentMethodComponent('braintree_paypal');
            },

            _placeOrder: function () {
                $(this.selectors.default).trigger('click');
                customerData.invalidate(['customer']);
            },

            isPlaceOrderActionAllowed: function () {
                return true;
            }
        });
    }
);