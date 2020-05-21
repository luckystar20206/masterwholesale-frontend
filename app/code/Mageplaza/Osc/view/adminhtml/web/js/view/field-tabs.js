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

define(['jquery'], function ($) {
    'use strict';

    $.widget('mageplaza.osc_field_tabs', {
        _create: function () {
            this.initTabEvent();
            this.initSaveEvent();
        },

        initTabEvent: function () {
            var elem = $('#mposc-field-tabs .action-default');

            elem.on('click', function () {
                elem.removeClass('_active');
                $(this).addClass('_active');

                $('.mposc-field-container').hide();
                $(this.getAttribute('href')).show();

                if ($(this).parent().children('.action-default._active').index() > 0) {
                    $('#add_customer_attr').hide();
                    $('#add_order_attr').show();
                } else {
                    $('#add_customer_attr').show();
                    $('#add_order_attr').hide();
                }

                return false;
            });

            if (window.location.hash) {
                $('[href=' + window.location.hash + ']').trigger('click');
            } else {
                $(elem[0]).trigger('click');
            }
        },

        initSaveEvent: function () {
            var self = this;

            $('.mposc-save-position').on('click', function () {
                self.savePosition(self.options.url);
            });
        },

        savePosition: function (url) {
            var self     = this,
                fields   = [],
                oaFields = [],
                field    = {},
                parent   = null;

            $('#position-save-messages').html('');

            $('.sorted-wrapper .sortable-item').each(function (index, el) {
                parent = $(el).parents('.mposc-field-container');

                field = {
                    code: $(el).attr('data-code'),
                    colspan: self.getColspan($(el)),
                    required: !!$(el).find('.attribute-required input').is(':checked')
                };

                if ($(el).parents('#mposc-address-information').length) {
                    fields.push(field);
                } else if (!$(el).hasClass('ui-state-disabled')) {
                    field.bottom =
                        parent.find('#' + $(el).attr('id')).index() > parent.find('.ui-state-disabled').index();

                    oaFields.push(field);
                }
            });

            $.ajax({
                method: 'post',
                showLoader: true,
                url: url,
                data: {
                    fields: JSON.stringify(fields),
                    oaFields: JSON.stringify(oaFields)
                },
                success: function (response) {
                    $('#position-save-messages').html(
                        '<div class="message message-' + response.type + ' ' + response.type + ' ">' +
                        '<span>' + response.message + '</span>' +
                        '</div>'
                    );
                }
            });
        },

        getColspan: function (elem) {
            if (elem.hasClass('wide')) {
                return 12;
            } else if (elem.hasClass('medium')) {
                return 9;
            } else if (elem.hasClass('short')) {
                return 3;
            }

            return 6;
        }
    });

    return $.mageplaza.osc_field_tabs;
});
