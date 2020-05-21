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

namespace Mageplaza\Osc\Model\Plugin\OrderAttributes;

use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Attribute;
use Mageplaza\OrderAttributes\Model\Config\Source\Position;
use Mageplaza\Osc\Helper\Address;

/**
 * Class Helper
 * @package Mageplaza\Osc\Model\Plugin\OrderAttributes
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
    public function afterGetFilteredAttributes(Data $subject, $result)
    {
        if (!$this->helper->isOscPage()) {
            return $result;
        }

        $sortOrder = 0;

        $position = [];
        foreach ($this->helper->getOAFieldPosition() as $item) {
            $position[$item['code']] = $item;
        }

        $attributes = [];

        foreach ($position as $code => $oaField) {
            foreach ($result as $attribute) {
                $pos = (int) $attribute->getPosition();

                if ($pos !== Position::ADDRESS && $attribute->getAttributeCode() !== $code) {
                    continue;
                }

                switch ($pos) {
                    case Position::SHIPPING_TOP:
                    case Position::SHIPPING_BOTTOM:
                        $pos = !empty($oaField['bottom']) ? Position::SHIPPING_BOTTOM : Position::SHIPPING_TOP;
                        break;
                    case Position::PAYMENT_TOP:
                    case Position::PAYMENT_BOTTOM:
                        $pos = !empty($oaField['bottom']) ? Position::PAYMENT_BOTTOM : Position::PAYMENT_TOP;
                        break;
                }
                $attribute->setPosition($pos);
                $attribute->setSortOrder($sortOrder++);
                $attribute->setIsRequired($oaField['required']);

                $attributes[] = $attribute;

                continue;
            }
        }

        return $attributes;
    }
}
