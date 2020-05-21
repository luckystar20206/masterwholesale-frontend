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

namespace Mageplaza\Osc\Model\Plugin\CustomerAttributes;

use Mageplaza\CustomerAttributes\Helper\Data;
use Magento\Eav\Model\Attribute;
use Mageplaza\Osc\Helper\Address;

/**
 * Class Helper
 * @package Mageplaza\Osc\Model\Plugin\CustomerAttributes
 */
class Helper
{
    /**
     * @var Address
     */
    private $helper;

    /**
     * Helper constructor.
     *
     * @param Address $helper
     */
    public function __construct(Address $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Data $subject
     * @param Attribute[] $result
     *
     * @return Attribute[]
     */
    public function afterGetAttributeWithFilters(Data $subject, $result)
    {
        if (!$this->helper->isOscPage()) {
            return $result;
        }

        $position = [];
        foreach ($this->helper->getFieldPosition() as $item) {
            $position[$item['code']] = $item;
        }

        $attributes = [];

        foreach ($result as $attribute) {
            if (!isset($position[$attribute->getAttributeCode()])) {
                continue;
            }

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
