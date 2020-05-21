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

        var cacheKey = 'comment';

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/container/review/comment'
            },
            commentValue: ko.observable(),
            initialize: function () {
                this._super();

                this.commentValue(oscData.getData(cacheKey));

                this.commentValue.subscribe(function (newValue) {
                    oscData.setData(cacheKey, newValue);
                });

                return this;
            }
        });
    }
);
