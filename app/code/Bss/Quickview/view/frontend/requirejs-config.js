/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Quickview
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
var config = {
    map: {
        '*': {
            bss_fancybox: 'Bss_Quickview/js/jquery.fancybox',
            bss_config: 'Bss_Quickview/js/bss_config',
            magnificPopup: 'Bss_Quickview/js/jquery.magnific-popup.min',
            bss_tocart: 'Bss_Quickview/js/bss_tocart'
        }
    },
    shim: {
        magnificPopup: {
            deps: ['jquery']
        }
    }
};
