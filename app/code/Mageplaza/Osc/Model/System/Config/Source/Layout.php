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
 * Class Layout
 * @package Mageplaza\Osc\Model\System\Config\Source
 */
class Layout implements ArrayInterface
{
    const ONE_COLUMN                 = '1column';
    const TWO_COLUMNS                = '2columns';
    const TWO_COLUMNS_FLOATING       = '2columns-floating';
    const THREE_COLUMNS              = '3columns';
    const THREE_COLUMNS_WITH_COLSPAN = '3columns-colspan';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('1 Column'),
                'value' => self::ONE_COLUMN
            ],
            [
                'label' => __('2 Columns'),
                'value' => self::TWO_COLUMNS
            ],
            [
                'label' => __('2 Columns With Floating Column'),
                'value' => self::TWO_COLUMNS_FLOATING
            ],
            [
                'label' => __('3 Columns'),
                'value' => self::THREE_COLUMNS
            ],
            [
                'label' => __('3 Columns With Colspan'),
                'value' => self::THREE_COLUMNS_WITH_COLSPAN
            ]
        ];

        return $options;
    }
}
