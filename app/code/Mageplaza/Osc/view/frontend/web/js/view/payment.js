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

define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Mageplaza_Osc/js/model/checkout-data-resolver',
        'Mageplaza_Osc/js/model/payment-service',
        'Mageplaza_Osc/js/model/paypal_express_compatible',
        'Mageplaza_Osc/js/model/braintree_paypal_gene',
        'Magento_Customer/js/customer-data',
        'mage/translate'
    ],
    function (ko,
              $,
              Component,
              quote,
              stepNavigator,
              additionalValidators,
              oscDataResolver,
              oscPaymentService,
              paypalExpressCompatible,
              braintreePaypalGene,
              customerData) {
        'use strict';

        oscDataResolver.resolveDefaultPaymentMethod();
        var isReload = true;

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/container/payment'
            },
            isLoading: oscPaymentService.isLoading,
            errorValidationMessage: ko.observable(false),

            initialize: function () {
                var self = this;

                this._super();

                stepNavigator.steps.removeAll();

                additionalValidators.registerValidator(this);

                quote.paymentMethod.subscribe(function (value) {
                    paypalExpressCompatible.togglePlaceOrderButton(quote.paymentMethod());
                    braintreePaypalGene.togglePlaceOrderButton(quote.paymentMethod());
                    self.errorValidationMessage(false);
                });

                if ($('.page.messages')) {
                    setTimeout(function () {
                        $('.page.messages').remove()
                    }, 8000);
                }

                if (isReload) {
                    customerData.reload(['cart'], false);
                    isReload = false;
                }
                this.customer = customerData.get('cart');

                return this;
            },

            validate: function () {
                if (!quote.paymentMethod()) {
                    this.errorValidationMessage($.mage.__('Please specify a payment method.'));

                    return false;
                }

                return true;
            }
        });
    }
);
