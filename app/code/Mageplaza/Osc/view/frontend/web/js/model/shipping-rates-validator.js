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
        'underscore',
        'jquery',
        'mageUtils',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rate-service',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/postcode-validator',
        'mage/translate',
        'uiRegistry'
    ],
    function (_,
              $,
              utils,
              addressList,
              Validator,
              shippingRatesValidationRules,
              addressConverter,
              selectShippingAddress,
              shippingRateService,
              shippingService,
              postcodeValidator,
              $t,
              uiRegistry) {
        'use strict';

        var countryElement = null,
            postcodeElement = null,
            postcodeElementName = 'postcode',
            observedElements = [],
            observableElements,
            defaultRules = {'rate': {'postcode': {'required': true}, 'country_id': {'required': true}}},
            addressFields = window.checkoutConfig.oscConfig.addressFields;

        return _.extend(Validator, {
            isFormInline: function () {
                return addressList().length === 0;
            },

            getValidationRules: function () {
                var rules = shippingRatesValidationRules.getRules();

                return _.isEmpty(rules) ? defaultRules : rules;
            },

            oscValidateAddressData: function (field, address) {
                var self = this,
                    canLoad = false;

                $.each(self.getValidationRules(), function (carrier, fields) {
                    if (fields.hasOwnProperty(field)) {
                        var missingValue = false;
                        $.each(fields, function (key, rule) {
                            if (self.isFieldExisted(key) && address.hasOwnProperty(key) && rule.required && utils.isEmpty(address[key])) {
                                var regionFields = ['region', 'region_id', 'region_id_input'];
                                if ($.inArray(key, regionFields) === -1
                                    || utils.isEmpty(address['region']) && utils.isEmpty(address['region_id'])
                                ) {
                                    missingValue = true;

                                    return false;
                                }
                            }
                        });
                        if (!missingValue) {
                            canLoad = true;

                            return false;
                        }
                    }
                });

                return canLoad;
            },

            isFieldExisted: function (field) {
                var result = false;
                $.each(observedElements, function (key, element) {
                    if (field === element.index) {
                        result = true;
                        return false;
                    }
                });

                return result;
            },

            /**
             * Perform postponed binding for fieldset elements
             *
             * @param {String} formPath
             */
            initFields: function (formPath) {
                var self = this;

                observableElements = shippingRatesValidationRules.getObservableFields();
                if (_.isEmpty(observableElements)) {
                    observableElements.push('country_id');
                }

                if ($.inArray(postcodeElementName, observableElements) === -1) {
                    // Add postcode field to observables if not exist for zip code validation support
                    observableElements.push(postcodeElementName);
                }

                $.each(addressFields, function (index, field) {
                    uiRegistry.async(formPath + '.' + field)(self.oscBindHandler.bind(self));
                });
            },

            oscBindHandler: function (element) {
                var self = this;

                if (element.component.indexOf('/group') !== -1) {
                    $.each(element.elems(), function (index, elem) {
                        uiRegistry.async(elem.name)(function () {
                            self.oscBindHandler(elem);
                        });
                    });
                } else if (element && element.hasOwnProperty('value')) {
                    element.on('value', function () {
                        self.oscPostcodeValidation();

                        if (self.isFormInline()) {
                            var addressFlat = addressConverter.formDataProviderToFlatData(
                                self.oscCollectObservedData(),
                                'shippingAddress'
                                ),
                                address;

                            address = addressConverter.formAddressDataToQuoteAddress(addressFlat);
                            selectShippingAddress(address);

                            if ($.inArray(element.index, observableElements) !== -1 && self.oscValidateAddressData(element.index, addressFlat)) {
                                shippingRateService.isAddressChange = true;

                                clearTimeout(self.validateAddressTimeout);
                                self.validateAddressTimeout = setTimeout(function () {
                                    shippingRateService.estimateShippingMethod();
                                }, 200);
                            }
                        }
                    });
                    observedElements.push(element);
                    if (element.index === postcodeElementName) {
                        postcodeElement = element;
                    }
                    if (element.index === 'country_id') {
                        countryElement = element;
                    }
                }
            },

            /**
             * Collect observed fields data to object
             *
             * @returns {*}
             */
            oscCollectObservedData: function () {
                var observedValues = {};

                $.each(observedElements, function (index, field) {
                    var value = field.value();
                    if ($.type(value) === 'undefined') {
                        value = '';
                    }
                    observedValues[field.dataScope] = value;
                });

                return observedValues;
            },

            oscPostcodeValidation: function () {
                var countryId = countryElement.value(),
                    validationResult,
                    warnMessage;

                if (postcodeElement === null || postcodeElement.value() === null) {
                    return true;
                }

                postcodeElement.warn(null);
                validationResult = postcodeValidator.validate(postcodeElement.value(), countryId);

                if (!validationResult) {
                    warnMessage = $t('Provided Zip/Postal Code seems to be invalid.');

                    if (postcodeValidator.validatedPostCodeExample.length) {
                        warnMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ') + '. ';
                    }
                    warnMessage += $t('If you believe it is the right one you can ignore this notice.');
                    postcodeElement.warn(warnMessage);
                }

                return validationResult;
            }
        });
    }
);
