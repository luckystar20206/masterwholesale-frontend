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
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/totals',
        'Mageplaza_Osc/js/model/payment-service'
    ],
    function ($, shippingService, totalService, paymentService) {
        'use strict';

        var blockLoader = {
            shipping: {
                queue: 0,
                service: shippingService
            },
            payment: {
                queue: 0,
                service: paymentService
            },
            total: {
                queue: 0,
                service: totalService
            }
        };

        return {
            getServices: function (blocks) {
                var services = {
                    payment: blockLoader.payment.service,
                    total: blockLoader.total.service
                };

                if (typeof blocks !== 'undefined') {
                    services = {};
                    $.each(blocks, function (index, block) {
                        if (blockLoader.hasOwnProperty(block)) {
                            services[block] = blockLoader[block].service;
                        }
                    });
                }

                return services;
            },

            /**
             * Start full page loader action
             */
            startLoader: function (blocks) {
                var services = this.getServices(blocks);
                $.each(services, function (index, service) {
                    blockLoader[index].queue += 1;
                    service.isLoading(true);
                });
            },

            /**
             * Stop full page loader action
             */
            stopLoader: function (blocks) {
                var services = this.getServices(blocks);
                $.each(services, function (index, service) {
                    blockLoader[index].queue -= 1;
                    if (blockLoader[index].queue == 0) {
                        service.isLoading(false);
                    }
                });
            }
        };
    }
);
