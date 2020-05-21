define([
    'jquery'
], function ($) {
    "use strict";
    $.widget('smartwave.infiniteScroll', {
        options: {
            productsListSelector: '#layer-product-list',
            next_page: '',
            loading: false,
            infinite_loaded_count: 0,
            active: false
        },
        _create: function () {
            this.inFinite();
        },
        inFinite: function(){
            var self = this;
            self.options.next_page = "";
            if($(self.options.productsListSelector +' .infinite-loader').length > 0){
                self.options.active = true;
            }
            $(".pages-items li > a.next").each(function(){
                self.options.next_page = $(this).attr("href");
            });
            $(window).scroll(function(){
                if(!self.options.loading && self.options.active && self.options.next_page && $(window).scrollTop() >= $(".infinite-loader").offset().top-$(window).height()+100){
                    if(self.options.infinite_loaded_count < 2){
                        self.options.loading = true;
                        $(".pages-items li > a.next").each(function(){
                            self.options.next_page = $(this).attr("href");
                        });
                        self.ajaxInfinite(self.options.next_page);
                    }
                }
            });
            $(".infinite-loader .btn-load-more").click(function(){
                if(!self.options.loading && self.options.next_page && self.options.infinite_loaded_count >= 2){
                    self.options.loading = true;
                    self.ajaxInfinite(self.options.next_page);
                }
            });
        },
        ajaxInfinite: function (submitUrl) {
            var self = this;
            self.options.infinite_loaded_count++;
            $('.infinite-loader .btn-load-more').hide();
            $('.infinite-loader .loading').fadeIn(300);
            $.ajax({
                type: 'GET',
                url: submitUrl,
                dataType: "html",
                beforeSend: function () {
                    $('.infinite-loader .btn-load-more').hide();
                    $('.infinite-loader .loading').fadeIn(300);
                },
                success: function (res) {
                    self.options.loading = false;
                    var items = $(res).find(self.options.productsListSelector +' .product-items .item');
                    var b = $('<div/>').html($(res).find(self.options.productsListSelector));
                    if (items.length > 0) {
                        if($(b).find('div.products-grid')){
                            $(self.options.productsListSelector + ' .products.wrapper').last()
                                .after($(res).find(self.options.productsListSelector +' div.products-grid.wrapper').detach())
                                .trigger('contentUpdated');
                        }
                        if($(b).find('div.products-list')){
                            $(self.options.productsListSelector + ' .products.wrapper').last()
                                .after($(res).find(self.options.productsListSelector +' div.products-list.wrapper').detach())
                                .trigger('contentUpdated');
                        }
                        if($(b).find('.pages a.next').length>0){
                            $(self.options.productsListSelector + ' .pages a.next').attr('href', $(res).find('.pages a.next').attr('href'));
                        }else{
                            $(self.options.productsListSelector + ' .pages a.next').remove();
                        }
                        $(b).remove();
                        $("form[data-role=tocart-form]").catalogAddToCart();
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
                        self.options.next_page = "";
                        $(".pages-items li > a.next").each(function(){
                            self.options.next_page = $(this).attr("href");
                        });
                        if(self.options.infinite_loaded_count >= 2){
                            $('.infinite-loader .loading').hide();
                            if(self.options.next_page){
                                $('.infinite-loader .btn-load-more').show();
                                $(".infinite-loader .btn-load-more").unbind("click").click(function(){
                                    if(!self.options.loading && self.options.next_page && self.options.infinite_loaded_count >= 2){
                                        self.options.loading = true;
                                        self.ajaxInfinite(self.options.next_page);
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
        }
    });
    return $.smartwave.infiniteScroll;
});