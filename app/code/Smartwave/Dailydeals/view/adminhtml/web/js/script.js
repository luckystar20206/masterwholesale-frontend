require([
    'jquery',
    'jquery_chosen'
], function ($) {
    'use strict';

    $(document).ready(function(){
    	setTimeout(function(){
    		$("#dailydeal_sw_product_sku").chosen();
    	},1000);
    });

});