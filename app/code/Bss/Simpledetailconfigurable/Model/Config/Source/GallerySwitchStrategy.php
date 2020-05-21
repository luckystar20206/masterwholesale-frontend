<?php
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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Model\Config\Source;

class GallerySwitchStrategy implements \Magento\Framework\Option\ArrayInterface
{
    const CONFIG_REPLACE = 'replace';

    const CONFIG_PREPEND = 'prepend';

    const CONFIG_DISABLED = 'disabled';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CONFIG_REPLACE, 'label' => __('Replace')],
            ['value' => self::CONFIG_PREPEND, 'label' => __('Prepend')],
            ['value' => self::CONFIG_DISABLED, 'label' => __('Disabled')]
        ];
    }
}
