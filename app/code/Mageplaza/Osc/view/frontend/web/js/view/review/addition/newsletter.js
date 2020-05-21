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
        'uiComponent',
        'Mageplaza_Osc/js/model/osc-data'
    ],
    function (ko, Component, oscData) {
        "use strict";

        var cacheKey = 'is_subscribed';

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/container/review/addition/newsletter'
            },
            initObservable: function () {
                this._super()
                    .observe({
                        isRegisterNewsletter: (typeof oscData.getData(cacheKey) === 'undefined') ? window.checkoutConfig.oscConfig.newsletterDefault : oscData.getData(cacheKey)
                    });
                oscData.setData(cacheKey, this.isRegisterNewsletter());
                this.isRegisterNewsletter.subscribe(function (newValue) {
                    oscData.setData(cacheKey, newValue);
                });

                return this;
            }
        });
    }
);
