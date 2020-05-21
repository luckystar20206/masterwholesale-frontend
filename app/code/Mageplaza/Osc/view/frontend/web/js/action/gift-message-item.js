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
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Mageplaza_Osc/js/model/resource-url-manager',
        'Mageplaza_Osc/js/model/gift-message',
        'mage/storage'
    ],
    function ($,
              quote,
              resourceUrlManager,
              giftMessageModel,
              storage) {
        'use strict';

        var giftMessageItems = window.checkoutConfig.oscConfig.giftMessageOptions.giftMessage.itemLevel,
            giftMessageModel = new giftMessageModel();

        return function (data, itemId, remove) {
            return storage.post(
                resourceUrlManager.getUrlForGiftMessageItemInformation(quote, itemId),
                JSON.stringify(data)
            ).done(
                function (response) {
                    if (response == true) {
                        if (remove) {
                            delete giftMessageItems[itemId].message;
                            giftMessageModel.showMessage('success', 'Delete gift message item success.');
                            return this;
                        }
                        giftMessageItems[itemId]['message'] = data.gift_message;
                        giftMessageModel.showMessage('success', 'Update gift message item success.');
                    }
                }
            ).fail(
                function () {
                    if (remove) {
                        giftMessageModel.showMessage('error', 'Can not delete gift message item. Please try again!');
                    }
                    giftMessageModel.showMessage('error', 'Can not update gift message item. Please try again!');
                }
            )
        };
    }
);
