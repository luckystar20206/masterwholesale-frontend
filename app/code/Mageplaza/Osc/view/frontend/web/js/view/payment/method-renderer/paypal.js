/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'Magento_Braintree/js/view/payment/method-renderer/paypal'
], function (
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageplaza_Osc/payment/braintree_paypal',
        },
    });
});

