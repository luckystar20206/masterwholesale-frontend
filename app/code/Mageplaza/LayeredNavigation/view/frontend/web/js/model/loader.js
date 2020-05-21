/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery'
    ],
    function ($) {
        'use strict';

        return {
            /**
             * Start full page loader action
             */
            startLoader: function () {
                $('#ln_overlay').show();
            },

            /**
             * Stop full page loader action
             */
            stopLoader: function () {
                $('#ln_overlay').hide();
            }
        };
    }
);
