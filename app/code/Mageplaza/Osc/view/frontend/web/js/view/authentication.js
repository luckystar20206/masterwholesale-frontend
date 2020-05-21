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
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Customer/js/action/login',
        'Magento_Customer/js/model/customer',
        'mage/translate',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/authentication-messages',
        'uiRegistry',
        'mage/validation'
    ],
    function ($, ko, Component, loginAction, customer, $t, modal, messageContainer, uiRegistry) {
        'use strict';

        var checkoutConfig = window.checkoutConfig;
        var emailElement = ('.popup-authentication #login-email'),
            passwordElement = ('.popup-authentication #login-password'),
            emailScope = "checkout.steps.shipping-step.shippingAddress.customer-email";

        return Component.extend({
            registerUrl: checkoutConfig.registerUrl,
            forgotPasswordUrl: checkoutConfig.forgotPasswordUrl,
            autocomplete: checkoutConfig.autocomplete,
            modalWindow: null,
            isLoading: ko.observable(false),

            defaults: {
                template: 'Mageplaza_Osc/container/authentication',
                email: ko.observable()
            },

            /**
             * Init
             */
            initialize: function () {
                var self = this;
                this._super();
                loginAction.registerLoginCallback(function () {
                    self.isLoading(false);
                });
            },

            /** Init popup login window */
            setModalElement: function (element) {
                this.modalWindow = element;
                var self = this;
                var options = {
                    'type': 'popup',
                    'title': $t('Sign In'),
                    'modalClass': 'popup-authentication',
                    'responsive': true,
                    'innerScroll': true,
                    'trigger': '.osc-authentication-toggle',
                    'buttons': [],
                    'opened': function () {
                        self.email(
                            uiRegistry.get(emailScope).email()
                        );
                    }
                };
                if (window.checkoutConfig.oscConfig.isDisplaySocialLogin && $("#social-login-popup").length > 0) {
                    this.modalWindow = $("#social-login-popup");
                    options.modalClass = 'osc-social-login-popup';
                }
                modal(options, $(this.modalWindow));
            },

            /** Is login form enabled for current customer */
            isActive: function () {
                return !customer.isLoggedIn();
            },

            /** Show login popup window */
            showModal: function () {
                $(this.modalWindow).modal('openModal');
            },

            /** Provide login action */
            login: function (loginForm) {
                var loginData = {},
                    formDataArray = $(loginForm).serializeArray();
                formDataArray.forEach(function (entry) {
                    loginData[entry.name] = entry.value;
                });

                if ($(loginForm).validation() &&
                    $(loginForm).validation('isValid')
                ) {
                    this.isLoading(true);
                    loginAction(loginData, null, false, messageContainer)
                        .done(function (response) {
                            if (!response.errors) {
                                messageContainer.addSuccessMessage({'message': $t('Login successfully. Please wait...')});
                            }
                        });
                }
            },

            /** Move label element when input has value */
            hasValue: function () {
                if (window.checkoutConfig.oscConfig.isUsedMaterialDesign) {
                    $(emailElement).val() ? $(emailElement).addClass('active') : $(emailElement).removeClass('active');
                    $(passwordElement).val() ? $(passwordElement).addClass('active') : $(passwordElement).removeClass('active');
                }
            }
        });
    }
);
