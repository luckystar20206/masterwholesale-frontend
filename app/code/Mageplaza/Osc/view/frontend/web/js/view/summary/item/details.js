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
    'ko',
    'underscore',
    'jquery',
    'Magento_Checkout/js/view/summary/item/details',
    'Magento_Checkout/js/model/quote',
    'Mageplaza_Osc/js/action/update-item',
    'Mageplaza_Osc/js/action/gift-message-item',
    'mage/url',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Mageplaza_Osc/js/action/payment-total-information',
    'Mageplaza_Osc/js/options/configurable',
    'priceOptions'
], function (ko, _, $, Component, quote, updateItemAction, giftMessageItem, url, $t, modal, getPaymentTotalInformation) {
    "use strict";

    var giftMessageOptions = window.checkoutConfig.oscConfig.giftMessageOptions,
        qtyIncrements      = window.checkoutConfig.oscConfig.qtyIncrements;

    return Component.extend({
        defaults: {
            template: 'Mageplaza_Osc/container/summary/item/details'
        },
        giftMessageItemsTitleHover: $t('Gift message item'),
        updateQtyDelay: 500,
        updateQtyTimeout: 0,
        itemOptions: {},

        getQuoteItem: function (itemId, items) {
            var item = _.find(items, function (quoteItem) {
                return Number(quoteItem.item_id) === Number(itemId);
            });

            return item ? item : {};
        },

        /**
         * Get product url
         * @param item
         * @returns {*}
         */
        getProductUrl: function (item) {
            var quoteItem = this.getQuoteItem(item.item_id, quote.getItems());

            if (item.hasOwnProperty('request_path') && item.request_path) {
                return item.request_path;
            }

            if (quoteItem && quoteItem.hasOwnProperty('product') && quoteItem.product.request_path) {
                return url.build(quoteItem.product.request_path);
            }

            return false;
        },

        /**
         * Init popup gift message item window
         * @param element
         * @param item_id
         */
        setModalElement: function (element, item_id) {
            var self    = this,
                options = {
                    'type': 'popup',
                    'title': $t('Gift Message Item &#40' + element.title + '&#41'),
                    'modalClass': 'popup-gift-message-item',
                    'responsive': true,
                    'innerScroll': true,
                    'trigger': '#' + element.id,
                    'buttons': [],
                    'opened': function () {
                        self.loadGiftMessageItem(item_id);
                    }
                };

            this.modalWindow = element;
            modal(options, $(this.modalWindow));
        },

        /**
         * Load exist gift message item from
         * @param itemId
         */
        loadGiftMessageItem: function (itemId) {
            var item;

            $('.popup-gift-message-item._show #item' + itemId).find('input:text,textarea').val('');
            if (giftMessageOptions.giftMessage.itemLevel[itemId].hasOwnProperty('message')
                && typeof giftMessageOptions.giftMessage.itemLevel[itemId]['message'] == 'object') {
                item = giftMessageOptions.giftMessage.itemLevel[itemId]['message'];

                $(this.createSelectorElement(itemId + ' #gift-message-whole-from')).val(item.sender);
                $(this.createSelectorElement(itemId + ' #gift-message-whole-to')).val(item.recipient);
                $(this.createSelectorElement(itemId + ' #gift-message-whole-message')).val(item.message);
                $(this.createSelectorElement(itemId + ' .action.delete')).show();

                return;
            }

            $(this.createSelectorElement(itemId + ' .action.delete')).hide();
        },

        /**
         * create selector element
         * @param selector
         * @returns {string}
         */
        createSelectorElement: function (selector) {
            return '.popup-gift-message-item._show #item' + selector;
        },

        /**
         * Update gift message item
         * @param itemId
         */
        updateGiftMessageItem: function (itemId) {
            var data = {
                gift_message: {
                    sender: $(this.createSelectorElement(itemId + ' #gift-message-whole-from')).val(),
                    recipient: $(this.createSelectorElement(itemId + ' #gift-message-whole-to')).val(),
                    message: $(this.createSelectorElement(itemId + ' #gift-message-whole-message')).val()
                }
            };

            giftMessageItem(data, itemId, false);
            this.closePopup();
        },

        /**
         * Delete gift message item
         * @param itemId
         */
        deleteGiftMessageItem: function (itemId) {
            giftMessageItem({
                gift_message: {
                    sender: '',
                    recipient: '',
                    message: ''
                }
            }, itemId, true);
            this.closePopup();
        },

        /**
         * Close popup gift message item
         */
        closePopup: function () {
            $('.action-close').trigger('click');
        },

        /**
         * Check item is available
         * @param itemId
         * @returns {boolean}
         */
        isItemAvailable: function (itemId) {
            var isGloballyAvailable,
                itemConfig,
                item = this.getQuoteItem(itemId, quote.getItems());

            if (Number(item.is_virtual) || !giftMessageOptions.isEnableOscGiftMessageItems) return false;

            // gift message product configuration must override system configuration
            isGloballyAvailable = this.getConfigValue('isItemLevelGiftOptionsEnabled');
            itemConfig          = giftMessageOptions.giftMessage.hasOwnProperty('itemLevel')
            && giftMessageOptions.giftMessage.itemLevel.hasOwnProperty(itemId) ?
                giftMessageOptions.giftMessage.itemLevel[itemId] : {};

            return itemConfig.hasOwnProperty('is_available') ? itemConfig['is_available'] : isGloballyAvailable;
        },

        getConfigValue: function (key) {
            return giftMessageOptions.hasOwnProperty(key) ?
                giftMessageOptions[key]
                : false;
        },

        /**
         * Plus item qty
         *
         * @param item
         * @param event
         */
        plusQty: function (item, event) {
            var self   = this,
                target = $(event.target).closest('td').find('.item_qty'),
                itemId = Number(target.attr("id")),
                qty    = Number(target.val());

            clearTimeout(this.updateQtyTimeout);

            if (qtyIncrements.hasOwnProperty(itemId)) {
                qty = (Math.floor(qty / qtyIncrements[itemId]) + 1) * qtyIncrements[itemId];
            } else {
                qty += 1;
            }

            target.val(qty);

            this.updateQtyTimeout = setTimeout(function () {
                self.updateItem(itemId, qty, target);
            }, this.updateQtyDelay);
        },

        /**
         * Minus item qty
         *
         * @param item
         * @param event
         */
        minusQty: function (item, event) {
            var self   = this,
                target = $(event.target).closest('td').find('.item_qty'),
                itemId = Number(target.attr("id")),
                qty    = Number(target.val());

            clearTimeout(this.updateQtyTimeout);

            if (qtyIncrements.hasOwnProperty(itemId)) {
                qty = (Math.ceil(qty / qtyIncrements[itemId]) - 1) * qtyIncrements[itemId];
            } else {
                qty -= 1;
            }

            target.val(qty);

            this.updateQtyTimeout = setTimeout(function () {
                self.updateItem(itemId, qty, target);
            }, this.updateQtyDelay);
        },

        /**
         * Change item qty in input box
         *
         * @param item
         * @param event
         */
        changeQty: function (item, event) {
            var target = $(event.target),
                itemId = Number(target.attr("id")),
                qty    = Number(target.val());

            if (qtyIncrements.hasOwnProperty(itemId) && qty % qtyIncrements[itemId]) {
                qty = (Math.ceil(qty / qtyIncrements[itemId]) - 1) * qtyIncrements[itemId];
            }

            this.updateItem(itemId, qty, target);
        },

        /**
         * Remove item by id
         *
         * @param itemId
         */
        removeItem: function (itemId) {
            this.updateItem(itemId);
        },

        /**
         * Send request update item
         *
         * @param itemId
         * @param itemQty
         * @param target
         * @returns {*}
         */
        updateItem: function (itemId, itemQty, target) {
            var self    = this,
                payload = {
                    item_id: itemId
                };

            if (typeof itemQty !== 'undefined') {
                payload['item_qty'] = itemQty;
            }

            updateItemAction(payload).fail(function () {
                target.val(self.getProductQty(itemId));
            });

            return this;
        },

        /**
         * Get product quantity
         * @param itemId
         * @returns {*}
         */
        getProductQty: function (itemId) {
            var item = this.getQuoteItem(itemId, quote.totals().items);

            if (item && item.hasOwnProperty('qty')) {
                return item.qty;
            }

            return 0;
        },

        setProductModal: function (element, item) {
            var self     = this,
                options,
                selector = '#mposc-product-modal-' + item.item_id;

            $('.mposc-product-modal-' + item.item_id).remove();

            options = {
                'type': 'popup',
                'title': item.name,
                'modalClass': 'mposc-product-modal mposc-product-modal-' + item.item_id,
                'responsive': true,
                'innerScroll': true,
                'trigger': '#button-edit-item-' + item.item_id,
                'buttons': [{
                    text: $t('Update'),
                    class: 'action primary',
                    click: function () {
                        if (!item.validation.valid()) {
                            return;
                        }

                        if ($(selector + ' > form').serialize() === self.itemOptions[item.item_id]) {
                            this.closeModal();
                        } else {
                            self.onUpdateCart(item);
                        }
                    }
                }, {
                    text: $t('Cancel'),
                    class: 'action',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };

            modal(options, $(selector));
        },

        showProductModal: function (item, data, event) {
            if (event.type !== 'click') {
                return;
            }

            $('#mposc-product-modal-' + item.item_id).modal('openModal');
        },

        initOptions: function (item) {
            var itemConfig = this.getItemConfig(item),
                selector   = '.mposc-product-modal-' + item.item_id + ' form',
                container  = $(selector);

            if (itemConfig.hasOwnProperty('configurableAttributes') && itemConfig.configurableAttributes) {
                container.mposcConfigurable({
                    spConfig: JSON.parse(itemConfig.configurableAttributes.spConfig),
                    superSelector: selector + ' .super-attribute-select'
                });
            }

            if (itemConfig.hasOwnProperty('customOptions') && itemConfig.customOptions) {
                container.priceOptions({
                    optionConfig: JSON.parse(itemConfig.customOptions.optionConfig)
                });
            }
            container.trigger('contentUpdated');

            item.form       = container;
            item.validation = container.validation();

            this.setItemOptionsData(item, container);
        },

        getConfigurableOptions: function (item) {
            var itemConfig = this.getItemConfig(item);

            if (itemConfig.hasOwnProperty('configurableAttributes')) {
                return itemConfig.configurableAttributes.template;
            }

            return '';
        },

        getCustomOptions: function (item) {
            var itemConfig = this.getItemConfig(item);

            if (itemConfig.hasOwnProperty('customOptions')) {
                return itemConfig.customOptions.template;
            }

            return '';
        },

        /**
         * @param item
         * @return {*}
         */
        getItemConfig: function (item) {
            var key = 'mposc', quoteItem;

            if (item.hasOwnProperty(key) && item[key]) {
                return item[key];
            }

            quoteItem = this.getQuoteItem(item.item_id, quote.getItems());

            if (quoteItem.hasOwnProperty(key) && quoteItem[key]) {
                return quoteItem[key];
            }

            return {};
        },

        onUpdateCart: function (item) {
            var self     = this,
                selector = '#mposc-product-modal-' + item.item_id,
                data     = new FormData(item.form[0]),
                newItem;

            data.append('qty', $('#' + item.item_id).val());

            $.ajax({
                type: 'POST',
                url: window.checkoutConfig.oscConfig.updateCartUrl + 'id/' + item.item_id,
                data: data,
                processData: false,
                contentType: false,
                showLoader: true,
                success: function (response) {
                    if (response.error) {
                        $(selector + ' > .message').show();
                        $(selector + ' > .message > span').text(response.error);

                        return;
                    }

                    self.setItemOptionsData(item, $(selector + ' > form'));
                    $(selector).modal('closeModal');

                    getPaymentTotalInformation().done(function (totals) {
                        newItem = self.getQuoteItem(item.item_id, totals.totals.items);

                        if (!newItem.item_id) {
                            $(selector).remove();
                        }
                    });
                }
            });
        },

        setItemOptionsData: function (item, form) {
            this.itemOptions[item.item_id] = form.serialize();
        }
    });
});
