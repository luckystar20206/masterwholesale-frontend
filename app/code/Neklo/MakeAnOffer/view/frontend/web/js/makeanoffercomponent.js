require([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Magento_Captcha/js/model/captcha',
    'Magento_Captcha/js/captcha',
    'Magento_Captcha/js/model/captchaList',
    'mage/translate'
], function ($, _, customerData, Captcha, captchaWidget, captchaList, $t) {
    'use strict';

    let config = {
        finalPriceSelector: '[data-price-type="finalPrice"]',
        basePriceSelector: '[data-price-type="basePrice"]',
        priceSelector: null,
        qtySelector: '#qty',
    };

    let formBehavior = '';
    let flagCaptcha = false;
    let flagOfferSent = false;

    $.fn.MakeAnOffer = function () {
        if ($(this).data('show-with-tax') === 3 && $(config.basePriceSelector).length > 0) {
            config.priceSelector = config.basePriceSelector;
        } else {
            config.priceSelector = config.finalPriceSelector;
        }

        //containers
        let $content = $(this).find('#make_an_offer_content');
        let $buttonGoTo = $('#make_an_offer_go-to');

        //checkers
        let checkOfferType = $content.hasClass('make-an-offer__container');

        //helpers
        let blockType = '';
        let price = '';
        let qty = '';
        let customerEmail = '';
        checkOfferType ? blockType = 'block' : blockType = 'popup';

        //messages
        let requiredMessage = $t('This is a required field.');
        let mailMessage = $t('Please enter a valid email address.');
        let numberMessage = $t('Please enter a valid number.');

        //component Make Offer
        let MO = {
            initClick: function (type) {
                if (type === 'block') {
                    let position = $content.offset().top;
                    formBehavior = 'block';
                    $buttonGoTo.on('click', function (event) {
                        if (!flagOfferSent) {
                            event.preventDefault();
                            MO.setupFields();
                            $('.make-an-offer__content').show();
                            $('body, html').animate({
                                scrollTop: position
                            }, 1000);
                        } else {
                            $('.make-an-offer-popup__container').show();
                        }
                    });
                } else {
                    let popup = $('.make-an-offer-popup'),
                        container = $('.make-an-offer-popup__container'),
                        close = $('.make-an-offer-popup__close');

                    formBehavior = 'popup';

                    $('#make_an_offer_show-popup').on('click', function (event) {

                        if ($('#product_addtocart_form').validation('isValid')) {
                            event.preventDefault();
                            MO.setupFields();
                            container.fadeIn(300).css('display', 'flex');

                            $('.make-an-offer-popup__content').show();
                            $('.make-an-offer-popup__message').hide();
                        }
                    });

                    close.on('click', function () {
                        container.fadeOut(300);
                    });

                    container.mousedown(function (event) {
                        event.stopPropagation();

                        if (!$(event.target).closest(popup).length) {
                            container.fadeOut(300);
                        }
                    });
                }
            },
            initClickForm: function () {
                $('#make_an_offer_button').on('click', function (event) {
                    event.preventDefault();
                    MO.sendData();
                });
            },
            isFormValid: function () {
                let isReqiredFildsValid = MO.checkRequiredFields();
                let isMailFildsValid = MO.checkEmailField();
                let isNumberFildsValid = MO.checkNumberFields();

                return isReqiredFildsValid && isMailFildsValid && isNumberFildsValid;
            },
            checkRequiredFields: function() {
                let $requiredFields = $content.find('input.required');

                MO.cleanFormErrors();

                let wrongFields = $requiredFields.filter(function (i, el) {
                    let value = el.value.trim();

                    if (value === '') {
                        MO.setErrorField(el, requiredMessage);
                    }

                    return !value;
                }).length;

                return wrongFields === 0;
            },
            checkEmailField: function() {
                let $emailFields = $content.find('input.email');

                let wrongFields = $emailFields.filter(function (i, el) {
                    let value = el.value.trim(),
                        isEmail = /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);

                    if (!isEmail) {
                        MO.setErrorField(el, mailMessage);
                    }

                    return !isEmail;
                }).length;

                return wrongFields === 0;
            },
            checkNumberFields: function() {
                let $numberFields = $content.find('input.number');

                let wrongFields = $numberFields.filter(function (i, el) {
                    let value = el.value.trim(),
                        isNumber = !isNaN(parseFloat(value)) && isFinite(value);

                    if (!isNumber) {
                        MO.setErrorField(el, numberMessage);
                    }

                    return !isNumber;
                }).length;

                return wrongFields === 0;
            },
            cleanFormErrors: function() {
                $content.find('.make-an-offer__error-field').each(function () {
                    $(this).removeClass('make-an-offer__error-field');
                });

                $content.find('.make-an-offer__error-message').each(function () {
                    $(this).remove();
                });
            },
            setErrorField: function(el, message) {
                var errorFeeld = $('<div />',{
                    text: message,
                    'class': 'make-an-offer__error-message'
                });

                $(el).addClass('make-an-offer__error-field');
                $(el).closest('.make-an-offer__line').append(errorFeeld);
            },
            setupFields: function () {
                MO.qty.get();
                MO.qty.set();
                MO.price.get();
                MO.price.set();
                MO.email.get();
                MO.email.set();
            },
            price: {
                get: function () {
                    price = $(config.priceSelector).data('price-amount');
                    price = price * qty;
					price = parseFloat(price).toFixed(2);

                    if (isNaN(price) || price === null) {
                        price = 0;
                    }
                },
                set: function () {
                    $('#makeanoffer-client-price').val(price);
                }
            },
            qty: {
                get: function () {
                    qty = $(config.qtySelector).val();
                    if (!qty) qty = 1;
                },
                set: function () {
                    $('#makeanoffer-qty').val(qty);
                },
            },
            email: {
                get: function () {
                    customerEmail = customerData.get('customer')().customerEmail;
                },
                set: function () {
                    $('#makeanoffer-email').val(customerEmail);
                }
            },
            hideForm: function () {
                if (formBehavior === 'block') {
                    $('.make-an-offer__content').hide();
                    MO.allowBlockPopupClick();
                } else {

                }
            },
            allowBlockPopupClick: function () {
                $('.make-an-offer-popup__container .make-an-offer-popup__close').click( function () {
                    $('.make-an-offer-popup__container').hide();
                });
            },
            allowPopupFormClick: function () {
                $('.make-an-offer-popup__container .make-an-offer-popup__close.dummy-close').click( function (event) {
                    $('.make-an-offer-popup__content').show();
                    $('.make-an-offer-popup__message').hide();
                    $('.make-an-offer-popup__container .make-an-offer-popup__close.dummy-close').hide();
                    $('.make-an-offer-popup__container .make-an-offer-popup__close:not(.dummy-close)').show();
                });
            },
            createDummyClose: function () {
                $('#make_an_offer_content').append($('.make-an-offer-popup__close').clone(false, false).addClass('dummy-close'));
                $('.make-an-offer-popup__container .make-an-offer-popup__close:not(.dummy-close)').hide();
            },
            showMessage: function (message) {
                let container = $('.make-an-offer-popup__container'),
                    content = $('.make-an-offer-popup__content'),
                    messageField = $('.make-an-offer-popup__message');

                container.fadeIn(300).css('display', 'flex');
                content.hide();
                $('#make_an_offer_content').removeClass('loading');
                messageField.html(message);
                messageField.show();
            },
            sendData: function () {
                if (MO.isFormValid()) {
                    let popupContent = $('#make_an_offer_content');
                    popupContent.addClass('loading');
                    let found_id;
                    let selected_options = {};
                    let product_type = $('#make_an_offer_product_type').data('makeanofferproductype');
                    if (product_type === 'configurable') {
                        $('div.swatch-attribute').each(function (k, v) {
                            let attribute_id = $(v).attr('attribute-id');
                            let option_selected = $(v).attr('option-selected');
                            if (!attribute_id || !option_selected) {
                                return;
                            }
                            selected_options[attribute_id] = option_selected;
                        });

                        //let product_id_index = $('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.index;
                        let product_id_index = $('[data-role=swatch-options]').data('bssSdcp').options.jsonConfig.index;

                        $.each(product_id_index, function (product_id, attributes) {
                            let productIsSelected = function (attributes, selected_options) {
                                return _.isEqual(attributes, selected_options);
                            };
                            if (productIsSelected(attributes, selected_options)) {
                                found_id = product_id;
                            }
                        });
                    }
                    let email = $('#makeanoffer-email').val(),
                        link = $('#makeanoffer-email-link').val(),
                        price = $('#makeanoffer-client-price').val(),
                        qty = $('#makeanoffer-qty').val(),
                        form_key = $('#makeanoffer-formkey').val(),
                        url = $('#make_an_offer_container').data('makeanofferurl'),
                        store_id = $('#make_an_offer_storeid').val(),
                        current_product = $('#make_an_offer_product_id').data('makeanofferproductid'),
                        current_price = parseFloat($(config.priceSelector).data('price-amount')).toFixed(2),
                        $capture = $('#captcha_make_an_offer_form'),
                        captureValue;

                    if ($capture.length) {
                        captureValue = $capture.val();
                    }

                    let data = {
                        form_key: form_key,
                        email: email,
                        link: link,
                        request_price: price,
                        qty: qty,
                        store_id: store_id,
                        current_price: current_price
                    };

                    if (captureValue) {
                        data.captcha = { make_an_offer_form: captureValue };
                    }

                    if (product_type === 'configurable') {
                        data.product_id = found_id;
                        data.product_options = selected_options;
                    } else {
                        data.product_id = current_product;
                    }
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        success: function (data) {
                            MO.showMessage(data.message);
                            flagCaptcha = false;
                            if (data.type === 'error') {
                                flagCaptcha = true;
                                MO.reloadCaptcha();
                            } else {
                                MO.hideForm();
                                // flagOfferSent = true;
                            }
                        },
                        error: function (data) {
                            MO.showMessage(data.message);
                        }
                    });
                }
            },
            reloadCaptcha: function () {
                if (flagCaptcha) {
                    if (formBehavior === 'block') {
                        MO.allowBlockPopupClick();
                    } else {
                        MO.createDummyClose();
                        MO.allowPopupFormClick();
                    }
                    $('#captcha-container-make_an_offer_form').captcha('refresh');
                }
            },
        };

        MO.initClick(blockType);
        MO.initClickForm();
    };

    $(document).ready(function () {
        $('#makeanoffer-main').MakeAnOffer();
    });
});
