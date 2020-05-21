/**
 * Copyright 2016 Mageplaza. All rights reserved.
 * See https://www.mageplaza.com/LICENSE.txt for license details.
 */
define([
    'jquery',
    'jquery/ui',
    'productListToolbarForm'
], function ($) {
    "use strict";
    var next_page = "";
    var loading = false;
    var infinite_loaded_count = 0;
    var active = false;
    $.widget('mageplaza.layer', {

        options: {
            productsListSelector: '#layer-product-list',
            navigationSelector: '#layered-filter-block'
        },

        _create: function () {
            this.initProductListUrl();
            this.initObserve();
            this.initLoading();
            this.inFinite();
        },

        initProductListUrl: function () {
            var self = this;
            $.mage.productListToolbarForm.prototype.changeUrl = function (paramName, paramValue, defaultValue) {
                var urlPaths = this.options.url.split('?'),
                    baseUrl = urlPaths[0],
                    urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                    paramData = {},
                    parameters;
                for (var i = 0; i < urlParams.length; i++) {
                    parameters = urlParams[i].split('=');
                    paramData[parameters[0]] = parameters[1] !== undefined
                        ? window.decodeURIComponent(parameters[1].replace(/\+/g, '%20'))
                        : '';
                }
                paramData[paramName] = paramValue;
                if (paramValue == defaultValue) {
                    delete paramData[paramName];
                }
                paramData = $.param(paramData);

                self.ajaxSubmit(baseUrl + (paramData.length ? '?' + paramData : ''));
            }
        },

        initObserve: function () {
            var self = this;
            var aElements = this.element.find('a');
            aElements.each(function (index) {
                var el = $(this);
                var link = self.checkUrl(el.prop('href'));
                if(!link) return;

                el.bind('click', function (e) {
                    if (el.hasClass('swatch-option-link-layered')) {
                        var childEl = el.find('.swatch-option');
                        childEl.addClass('selected');
                    } else {
                        var checkboxEl = el.find('input[type=checkbox]');
                        checkboxEl.prop('checked', !checkboxEl.prop('checked'));
                    }

                    self.ajaxSubmit(link);
                    e.stopPropagation();
                    e.preventDefault();
                });

                var checkbox = el.find('input[type=checkbox]');
                checkbox.bind('click', function (e) {
                    self.ajaxSubmit(link);
                    e.stopPropagation();
                });
            });

            $(".filter-current a").bind('click', function (e) {
                var link = self.checkUrl($(this).prop('href'));
                if(!link) return;

                self.ajaxSubmit(link);
                e.stopPropagation();
                e.preventDefault();
            });

            $(".filter-actions a").bind('click', function (e) {
                var link = self.checkUrl($(this).prop('href'));
                if(!link) return;

                self.ajaxSubmit(link);
                e.stopPropagation();
                e.preventDefault();
            });
        },

        checkUrl: function (url) {
            var regex = /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;

            return regex.test(url) ? url : null;
        },

        initLoading: function () {

        },

        inFinite: function() {
            var self = this;
            next_page = "";
            if($(self.options.productsListSelector +' .infinite-loader').length > 0){
                active = true;
            }
            $(".pages-items li > a.next").each(function(){
                next_page = $(this).attr("href");
            });
            $(window).scroll(function(){
                if(!loading && next_page && active && $(window).scrollTop() >= $(".infinite-loader").offset().top-$(window).height()+100){
                    if(infinite_loaded_count < 2){
                        loading = true;
                        $(".pages-items li > a.next").each(function(){
                            next_page = $(this).attr("href");
                        });
                        self.ajaxInfinite(next_page);
                    }
                }
            });
            $(".infinite-loader .btn-load-more").click(function(){
                if(!loading && next_page && infinite_loaded_count >= 2){
                    loading = true;
                    self.ajaxInfinite(next_page);
                }
            });
        },

        ajaxInfinite: function (submitUrl) {
            var self = this;
            infinite_loaded_count++;
            $('.infinite-loader .btn-load-more').hide();
            $('.infinite-loader .loading').fadeIn(300);
            $.ajax({
                url: submitUrl,
                data: {isAjax: 1},
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    $('.infinite-loader .btn-load-more').hide();
                    $('.infinite-loader .loading').fadeIn(300);
                },
                success: function (res) {
                    loading = false;
                    if (res.backUrl) {
                        window.location = res.backUrl;
                        return;
                    }
                    if (res.products) {
                        if($(res.products).find('div.products-grid')){
                            var items_grid = $(res.products).find('div.products-grid.wrapper .product-items .item');
                            $(self.options.productsListSelector + ' .products.wrapper .product-items').append(items_grid);
                            $(self.options.productsListSelector).trigger('contentUpdated');
                        }
                        if($(res.products).find('div.products-list')){
                            var items_list = $(res.products).find('div.products-list.wrapper .product-items .item');
                            $(self.options.productsListSelector + ' .products.wrapper .product-items').append(items_list);
                            $(self.options.productsListSelector).trigger('contentUpdated');
                        }
                        if($(res.products).find('.pages a.next').length > 0){
                            $(self.options.productsListSelector + ' .pages a.next').attr('href', $(res.products).find('.pages a.next').attr('href'));
                        }else{
                            $(self.options.productsListSelector + ' .pages a.next').remove();
                        }
                        if($("form[data-role=tocart-form]").length > 0) {
                            $("form[data-role=tocart-form]").catalogAddToCart();
                        }
                        $('.main .products.grid .product-items li.product-item:nth-child(2n)').addClass('nth-child-2n');
                        $('.main .products.grid .product-items li.product-item:nth-child(2n+1)').addClass('nth-child-2np1');
                        $('.main .products.grid .product-items li.product-item:nth-child(3n)').addClass('nth-child-3n');
                        $('.main .products.grid .product-items li.product-item:nth-child(3n+1)').addClass('nth-child-3np1');
                        $('.main .products.grid .product-items li.product-item:nth-child(4n)').addClass('nth-child-4n');
                        $('.main .products.grid .product-items li.product-item:nth-child(4n+1)').addClass('nth-child-4np1');
                        $('.main .products.grid .product-items li.product-item:nth-child(5n)').addClass('nth-child-5n');
                        $('.main .products.grid .product-items li.product-item:nth-child(5n+1)').addClass('nth-child-5np1');
                        $('.main .products.grid .product-items li.product-item:nth-child(6n)').addClass('nth-child-6n');
                        $('.main .products.grid .product-items li.product-item:nth-child(6n+1)').addClass('nth-child-6np1');
                        $('.main .products.grid .product-items li.product-item:nth-child(7n)').addClass('nth-child-7n');
                        $('.main .products.grid .product-items li.product-item:nth-child(7n+1)').addClass('nth-child-7np1');
                        $('.main .products.grid .product-items li.product-item:nth-child(8n)').addClass('nth-child-8n');
                        $('.main .products.grid .product-items li.product-item:nth-child(8n+1)').addClass('nth-child-8np1');
                        var hist = submitUrl;
                        if(submitUrl.indexOf("p=") > -1){
                            var len = submitUrl.length-submitUrl.indexOf("p=");
                            var str_temp = submitUrl.substr(submitUrl.indexOf("p="),len);
                            var page_param = "";
                            if(str_temp.indexOf("&") == -1){
                                page_param = str_temp;
                            } else {
                                page_param = str_temp.substr(0,str_temp.indexOf("&"));
                            }
                            hist = submitUrl.replace(page_param, "");
                        }
                        if (typeof window.history.pushState === 'function') {
                            window.history.pushState({url: hist}, '', hist);
                        }
                        if(typeof enable_quickview != 'undefined' && enable_quickview == true) {
                            requirejs(['jquery', 'weltpixel_quickview' ],
                                function($, quickview) {
                                    $('.weltpixel-quickview').off('click').on('click', function() {
                                        var prodUrl = $(this).attr('data-quickview-url');
                                        if (prodUrl.length) {
                                            quickview.displayContent(prodUrl);
                                        }
                                    });
                                });
                        }
                        $(".products-grid .weltpixel-quickview").each(function(){
                            $(this).appendTo($(this).parent().parent().children(".product-item-photo"));
                        });
                        $("#layer-product-list img.porto-lazyload:not(.porto-lazyload-loaded)").lazyload({effect:"fadeIn"});
                        if ($('#layer-product-list .porto-lazyload:not(.porto-lazyload-loaded)').closest('.owl-carousel').length) {
                            $('#layer-product-list .porto-lazyload:not(.porto-lazyload-loaded)').closest('.owl-carousel').on('initialized.owl.carousel', function() {
                                $(this).find('.porto-lazyload:not(.porto-lazyload-loaded)').trigger('appear');
                            });
                            $('#layer-product-list .porto-lazyload:not(.porto-lazyload-loaded)').closest('.owl-carousel').on('changed.owl.carousel', function() {
                                $(this).find('.porto-lazyload:not(.porto-lazyload-loaded)').trigger('appear');
                            });
                        }
                        next_page = "";
                        $(".pages-items li > a.next").each(function(){
                            next_page = $(this).attr("href");
                        });
                        if(infinite_loaded_count >= 2){
                            $('.infinite-loader .loading').hide();
                            if(next_page){
                                $('.infinite-loader .btn-load-more').show();
                                $(".infinite-loader .btn-load-more").unbind("click").click(function(){
                                    if(!loading && next_page && infinite_loaded_count >= 2){
                                        loading = true;
                                        self.ajaxInfinite(next_page);
                                    }
                                });
                            }
                        } else {
                            $('.infinite-loader .loading').fadeOut(300);
                        }
                    }

                },
                error: function () {
                    window.location.reload();
                }
            });
        },

        ajaxSubmit: function (submitUrl) {
            var self = this;
            infinite_loaded_count = 0;
            $.ajax({
                url: submitUrl,
                data: {isAjax: 1},
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    $('.ln_overlay').show();
                    if (typeof window.history.pushState === 'function') {
                        window.history.pushState({url: submitUrl}, '', submitUrl);
                    }
                },
                success: function (res) {
                    if (res.backUrl) {
                        window.location = res.backUrl;
                        return;
                    }
                    if (res.navigation) {
                        $(self.options.navigationSelector).replaceWith(res.navigation);
                        $(self.options.navigationSelector).trigger('contentUpdated');
                    }
                    if (res.products) {
                        $(self.options.productsListSelector).replaceWith(res.products);
                        $(self.options.productsListSelector).trigger('contentUpdated');
                    }
                    $('.ln_overlay').hide();
                    if(typeof enable_quickview != 'undefined' && enable_quickview == true) {
                        requirejs(['jquery', 'weltpixel_quickview' ],
                        function($, quickview) {
                            $('.weltpixel-quickview').off('click').on('click', function() {
                                var prodUrl = $(this).attr('data-quickview-url');
                                if (prodUrl.length) {
                                    quickview.displayContent(prodUrl);
                                }
                            });
                        });
                    }
                    $(".products-grid .weltpixel-quickview").each(function(){
                        $(this).appendTo($(this).parent().parent().children(".product-item-photo"));
                    });
                    $("#layer-product-list img.porto-lazyload").lazyload({effect:"fadeIn"});
                    if ($('#layer-product-list .porto-lazyload').closest('.owl-carousel').length) {
                        $('#layer-product-list .porto-lazyload').closest('.owl-carousel').on('changed.owl.carousel', function() {
                            $(this).find('.porto-lazyload:not(.porto-lazyload-loaded)').trigger('appear');
                        });
                    }
                },
                error: function () {
                    window.location.reload();
                }
            });
        }
    });

    return $.mageplaza.layer;
});
