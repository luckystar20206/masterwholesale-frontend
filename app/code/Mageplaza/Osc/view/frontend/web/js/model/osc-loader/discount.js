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
        'ko'
    ],
    function (ko) {
        'use strict';

        return {
            isLoading: ko.observable(false),

            /**
             * Start full page loader action
             */
            startLoader: function () {
                this.isLoading(true);
            },

            /**
             * Stop full page loader action
             */
            stopLoader: function () {
                this.isLoading(false);
            }
        };
    }
);
