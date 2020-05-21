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
 * @package     Mageplaza_DeliveryTime
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DeliveryTime\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DeliveryTime
 * @package Mageplaza\Osc\Model\System\Config\Source
 */
class DeliveryTime implements ArrayInterface
{
    const DAY_MONTH_YEAR_SLASH = 'dd/mm/yy';
    const DAY_MONTH_YEAR_DASH  = 'dd-mm-yy';
    const DAY_MONTH_YEAR_DOT   = 'dd.mm.yy';
    const MONTH_DAY_YEAR_SLASH = 'mm/dd/yy';
    const MONTH_DAY_YEAR_DASH  = 'mm-dd-yy';
    const MONTH_DAY_YEAR_DOT   = 'mm.dd.yy';
    const YEAR_MONTH_DAY_SLASH = 'yy/mm/dd';
    const YEAR_MONTH_DAY_DASH  = 'yy-mm-dd';
    const YEAR_MONTH_DAY_DOT   = 'yy.mm.dd';
    const SHORT_FORM           = 'd M, y';
    const MEDIUM_FORM          = 'd MM, y';
    const FULL_FORM            = 'DD, d MM, yy';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Day/Month/Year (%1)', date('d/m/Y')),
                'value' => self::DAY_MONTH_YEAR_SLASH
            ],
            [
                'label' => __('Day-Month-Year (%1)', date('d-m-Y')),
                'value' => self::DAY_MONTH_YEAR_DASH
            ],
            [
                'label' => __('Day.Month.Year (%1)', date('d.m.Y')),
                'value' => self::DAY_MONTH_YEAR_DOT
            ],
            [
                'label' => __('Month/Day/Year (%1)', date('m/d/Y')),
                'value' => self::MONTH_DAY_YEAR_SLASH
            ],
            [
                'label' => __('Month-Day-Year (%1)', date('m-d-Y')),
                'value' => self::MONTH_DAY_YEAR_DASH
            ],
            [
                'label' => __('Month.Day.Year (%1)', date('m.d.Y')),
                'value' => self::MONTH_DAY_YEAR_DOT
            ],
            [
                'label' => __('Year/Month/Day (%1)', date('Y/m/d')),
                'value' => self::YEAR_MONTH_DAY_SLASH
            ],
            [
                'label' => __('Year-Month-Day (%1)', date('Y-m-d')),
                'value' => self::YEAR_MONTH_DAY_DASH
            ],
            [
                'label' => __('Year.Month.Day (%1)', date('Y.m.d')),
                'value' => self::YEAR_MONTH_DAY_DOT
            ],
            [
                'label' => __('Short d M, y (%1)', date('d M, Y')),
                'value' => self::SHORT_FORM
            ],
            [
                'label' => __('Medium d MM, Y (%1)', date('d F, Y')),
                'value' => self::MEDIUM_FORM
            ],
            [
                'label' => __('Full DD, d MM, yy (%1)', date('l, d F, Y')),
                'value' => self::FULL_FORM
            ]
        ];

        return $options;
    }
}
