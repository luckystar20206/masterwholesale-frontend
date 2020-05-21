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

namespace Mageplaza\Osc\Model\Plugin\Catalog\Product\View\Type\Bundle;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;
use Mageplaza\Osc\Helper\Data;

/**
 * Class OptionPlugin
 * @package Mageplaza\Osc\Model\Plugin\Catalog\Product\View\Type\Bundle
 */
class OptionPlugin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * OptionPlugin constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Option $subject
     */
    public function beforeGetData(Option $subject)
    {
        if (class_exists('Magento\Bundle\Block\DataProviders\OptionPriceRenderer')) {
            $subject->setTierPriceRenderer(
                $this->helper->getObject('Magento\Bundle\Block\DataProviders\OptionPriceRenderer')
            );
        }
    }
}
