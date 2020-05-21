<?php
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

namespace Mageplaza\Osc\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class StaticBlockPosition
 * @package Mageplaza\Osc\Model\System\Config\Source
 */
class StaticBlockPosition implements ArrayInterface
{
    const NOT_SHOW                     = 0;
    const SHOW_IN_SUCCESS_PAGE         = 1;
    const SHOW_AT_TOP_CHECKOUT_PAGE    = 2;
    const SHOW_AT_BOTTOM_CHECKOUT_PAGE = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::NOT_SHOW                     => __('None'),
            self::SHOW_IN_SUCCESS_PAGE         => __('In Success Page'),
            self::SHOW_AT_TOP_CHECKOUT_PAGE    => __('At Top of Checkout Page'),
            self::SHOW_AT_BOTTOM_CHECKOUT_PAGE => __('At Bottom of Checkout Page')
        ];
    }
}
