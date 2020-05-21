/**
 * Copyright © 2016 Mageplaza. All rights reserved.
 * See https://www.mageplaza.com/LICENSE.txt for license details.
 */
define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'jquery/ui',
    'Mageplaza_LayeredNavigation/js/layer'
], function($, ultil) {
    "use strict";

    $.widget('mageplaza.layerSlider', $.mageplaza.layer, {
        options: {
            sliderElement: '#ln_price_slider',
            textElement: '#ln_price_text'
        },
        _create: function () {
            var self = this;
            $(this.options.sliderElement).slider({
                min: self.options.minValue,
                max: self.options.maxValue,
                values: [self.options.selectedFrom, self.options.selectedTo],
                slide: function( event, ui ) {
                    self.displayText(ui.values[0], ui.values[1]);
                },
                change: function(event, ui) {
                    self.ajaxSubmit(self.getUrl(ui.values[0], ui.values[1]));
                }
            });
            this.displayText(this.options.selectedFrom, this.options.selectedTo);
        },

        getUrl: function(from, to){
            return this.options.ajaxUrl.replace(encodeURI('{price_start}'), from).replace(encodeURI('{price_end}'), to);
        },

        displayText: function(from, to){
            $(this.options.textElement).html(this.formatPrice(from) + ' - ' + this.formatPrice(to));
        },

        formatPrice: function(value) {
            return ultil.formatPrice(value, this.options.priceFormat);
        }
    });

    return $.mageplaza.layerSlider;
});
