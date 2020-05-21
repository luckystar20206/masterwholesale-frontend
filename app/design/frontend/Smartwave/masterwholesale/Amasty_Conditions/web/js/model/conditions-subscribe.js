define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Amasty_Conditions/js/action/recollect-totals',
    'Amasty_Conditions/js/model/subscriber',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/totals',
    'Magento_SalesRule/js/view/payment/discount',
    'rjsResolver'
], function ($, _, Component, quote, recollect, subscriber, shippingService, shippingProcessor, totals, discount, resolver) {
    'use strict';

    return Component.extend({
        previousShippingMethodData: {},
        previousItemsData: [],
        billingAddressCountry: null,
        city: null,
        street: null,
        isPageLoaded: false,
        initialize: function () {
            this._insertPolyfills();
            this._super();

            resolver(function() {
                this.isPageLoaded = true;

                totals.getItems().subscribe(this.storeOldItems, this, "beforeChange");
                totals.getItems().subscribe(this.recollectOnItems, this);
            }.bind(this));

            discount().isApplied.subscribe(function () {
                recollect(true);
            });

            quote.shippingAddress.subscribe(function (newShippingAddress) {
                // while page is loading do not recollect, should be recollected after shipping rates
                // for avoid extra requests to server
                if (this.isPageLoaded && this._isNeededRecollectShipping(newShippingAddress, this.city, this.street)) {
                    this.city = newShippingAddress.city;
                    this.street = newShippingAddress.street;
                    if (newShippingAddress) {
                        recollect();
                    }
                }
            }.bind(this));

            quote.billingAddress.subscribe(function (newBillAddress) {
                if (this._isNeededRecollectBilling(
                    newBillAddress,
                    this.billingAddressCountry,
                    this.billingAddressCity
                )) {
                    this.billingAddressCountry = newBillAddress.countryId;
                    this.billingAddressCity = newBillAddress.city;
                    if (!this._isVirtualQuote()
                        && (quote.shippingAddress() && newBillAddress.countryId !== quote.shippingAddress().countryId)
                    ) {
                        shippingProcessor.getRates(quote.shippingAddress());
                    }
                    recollect();
                }
            }.bind(this));

            //for invalid shipping address update
            shippingService.getShippingRates().subscribe(function (rates) {
                if (!this._isVirtualQuote()) {
                    //recollect();
                }
            }.bind(this));

            quote.paymentMethod.subscribe(function (newMethodData) {
                recollect();
            }, this);

            quote.shippingMethod.subscribe(this.storeOldMethod, this, "beforeChange");
            quote.shippingMethod.subscribe(this.recollectOnShippingMethod, this);

            return this;
        },

        /**
         * Store before change shipping method, because sometimes shipping methods updates always (not by change)
         *
         * @param {Object} oldMethod
         */
        storeOldMethod: function (oldMethod) {
            this.previousShippingMethodData = oldMethod;
        },

        recollectOnShippingMethod: function (newMethodData) {
            if (!_.isEqual(this.previousShippingMethodData, newMethodData)) {
                recollect();
            }
        },

        /**
         * Store before change cart items
         *
         * @param {Array} oldItems
         * @since 1.3.13
         */
        storeOldItems: function (oldItems) {
            this.previousItemsData = this._prepareArrayForCompare(oldItems);
        },

        /**
         * Recollect totals on cart items update
         *
         * @param {Array} newItems
         * @since 1.3.13 improve compatibility with modules which allow update cart items on checkout page
         *        and ajax update cart items
         */
        recollectOnItems: function (newItems) {
            if (!_.isEqual(this.previousItemsData, this._prepareArrayForCompare(newItems))) {
                // totals should be already collected, trigger subscribers
                // for more stability but less performance can be replaced with recollect(true);
                subscriber.isLoading.valueHasMutated();
            }
        },

        /**
         * Remove all not simple types from array items
         *
         * @param {Array} data
         * @returns {Array}
         * @private
         * @since 1.3.13
         */
        _prepareArrayForCompare: function (data) {
            var result = [],
                itemData = {};

            _.each(data, function(item) {
                itemData = _.pick(item, function (value) {
                    return !_.isObject(value);
                });
                result.push(itemData);
            }.bind(this));

            return result;
        },

        _isVirtualQuote: function () {
            return quote.isVirtual()
                || window.checkoutConfig.activeCarriers && window.checkoutConfig.activeCarriers.length === 0;
        },

        _isNeededRecollectShipping: function (newShippingAddress, city, street) {
            return !this._isVirtualQuote()
                && (
                    newShippingAddress
                    && (newShippingAddress.city || newShippingAddress.street)
                    && (newShippingAddress.city != city || !_.isEqual(newShippingAddress.street, street)));
        },

        _isNeededRecollectBilling: function (newBillAddress, billingAddressCountry, billingAddressCity) {
            var isNeedRecollectByCountry = newBillAddress
                    && newBillAddress.countryId
                    && newBillAddress.countryId !== billingAddressCountry,
                isNeedRecollectByCity = newBillAddress
                    && newBillAddress.city
                    && newBillAddress.city !== billingAddressCity;

            return this.isPageLoaded && (isNeedRecollectByCountry || isNeedRecollectByCity);
        },

        _insertPolyfills: function () {
            if (typeof Object.assign != 'function') {
                // Must be writable: true, enumerable: false, configurable: true
                Object.defineProperty(Object, "assign", {
                    value: function assign(target, varArgs) { // .length of function is 2
                        'use strict';
                        if (target == null) { // TypeError if undefined or null
                            throw new TypeError('Cannot convert undefined or null to object');
                        }

                        var to = Object(target);

                        for (var index = 1; index < arguments.length; index++) {
                            var nextSource = arguments[index];

                            if (nextSource != null) { // Skip over if undefined or null
                                for (var nextKey in nextSource) {
                                    // Avoid bugs when hasOwnProperty is shadowed
                                    if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                                        to[nextKey] = nextSource[nextKey];
                                    }
                                }
                            }
                        }
                        return to;
                    },
                    writable: true,
                    configurable: true
                });
            }
        }
    });
});
