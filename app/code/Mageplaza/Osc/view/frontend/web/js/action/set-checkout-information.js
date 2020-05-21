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
        'Magento_Checkout/js/model/shipping-save-processor',
        'Mageplaza_Osc/js/model/shipping-save-processor/checkout'
    ],
    function (shippingSaveProcessor, oscProcessor) {
        'use strict';

        shippingSaveProcessor.registerProcessor('osc', oscProcessor);

        return function () {
            return shippingSaveProcessor.saveShippingInformation('osc');
        }
    }
);
