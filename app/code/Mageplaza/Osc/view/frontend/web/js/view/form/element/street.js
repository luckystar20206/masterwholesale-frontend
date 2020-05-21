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
    'Magento_Ui/js/form/element/abstract',
    'Mageplaza_Osc/js/model/address/google-auto-complete'
], function (Component, googleAutoComplete) {
    'use strict';

    return Component.extend({

        googleAutocomplete: null,

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super()
                .initAutocomplete();

            return this;
        },

        /**
         * Init google/pca autocomplete
         */
        initAutocomplete: function () {
            var fieldsetName = this.parentName.split('.').slice(0, -1).join('.');

            switch (window.checkoutConfig.oscConfig.autocomplete.type) {
                case 'google':
                    this.googleAutocomplete = new googleAutoComplete(this.uid, fieldsetName);
                    break;
                case 'pca':
                    break;
                default :
                    break;
            }

            return this;
        }
    });
});
