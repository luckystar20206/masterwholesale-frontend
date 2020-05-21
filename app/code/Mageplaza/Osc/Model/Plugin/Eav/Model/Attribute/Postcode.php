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

namespace Mageplaza\Osc\Model\Plugin\Eav\Model\Attribute;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Osc\Helper\Address;

/**
 * Class Postcode
 * @package Mageplaza\Osc\Model\Plugin\Eav\Model\Attribute
 */
class Postcode
{
    /**
     * @var Address
     */
    private $helper;

    /**
     * Postcode constructor.
     *
     * @param Address $helper
     */
    public function __construct(Address $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\Model\Attribute\Data\Postcode $subject
     * @param array|bool $result
     *
     * @return array|string
     * @throws LocalizedException
     */
    public function afterValidateValue(\Magento\Customer\Model\Attribute\Data\Postcode $subject, $result)
    {
        $attribute = $subject->getAttribute();

        foreach ($this->helper->getFieldPosition() as $item) {
            if ($item['code'] === $attribute->getAttributeCode()) {
                if (empty($item['required'])) {
                    return true;
                }

                return $result;
            }
        }

        return true;
    }
}
