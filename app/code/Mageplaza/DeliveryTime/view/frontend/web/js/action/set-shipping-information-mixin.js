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
 * @package     Mageplaza_DeliveryTime
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Mageplaza_DeliveryTime/js/model/delivery-information'
], function ($, wrapper, quote, deliveryInformation) {
    'use strict';

    return function (setShippingInformationAction) {
        if (!window.checkoutConfig || !window.checkoutConfig.mpDtConfig) {
            return setShippingInformationAction;
        }

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();

            if (!shippingAddress.hasOwnProperty('extension_attributes')) {
                shippingAddress.extension_attributes = {};
            }

            var deliveryData = {
                mp_delivery_date: deliveryInformation().deliveryDate(),
                mp_delivery_time: deliveryInformation().deliveryTime(),
                mp_house_security_code: deliveryInformation().houseSecurityCode(),
                mp_delivery_comment: deliveryInformation().deliveryComment()
            };

            shippingAddress.extension_attributes = $.extend(
                shippingAddress.extension_attributes,
                deliveryData
            );

            return originalAction();
        });
    };
});