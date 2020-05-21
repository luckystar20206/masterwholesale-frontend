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
 * Class SealBlock
 * @package Mageplaza\Osc\Model\System\Config\Source
 */
class SealBlockPosition implements ArrayInterface
{
    const NOT_SHOW            = 0;
    const SELECT_STATIC_BLOCK = 1;
    const USE_DEFAULT_DESIGN  = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::NOT_SHOW            => __('No'),
            self::SELECT_STATIC_BLOCK => __('Select Static Block'),
            self::USE_DEFAULT_DESIGN  => __('Use Default Design')
        ];
    }
}
