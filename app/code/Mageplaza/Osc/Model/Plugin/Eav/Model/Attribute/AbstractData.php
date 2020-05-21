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
 * Class AbstractData
 * @package Mageplaza\Osc\Model\Plugin\Eav\Model\Attribute
 */
class AbstractData
{
    /**
     * @var Address
     */
    private $helper;

    /**
     * AbstractData constructor.
     *
     * @param Address $helper
     */
    public function __construct(Address $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Eav\Model\Attribute\Data\AbstractData $subject
     * @param array|string $value
     *
     * @return array|string
     * @throws LocalizedException
     */
    public function beforeValidateValue(\Magento\Eav\Model\Attribute\Data\AbstractData $subject, $value)
    {
        if ($value === null) {
            $value = '';
        }

        $attribute = $subject->getAttribute();

        foreach ($this->helper->getFieldPosition() as $item) {
            if ($item['code'] === $attribute->getAttributeCode()) {
                if (empty($item['required'])) {
                    $attribute->setIsRequired(false);
                }

                return [$value];
            }
        }

        $attribute->setIsRequired(false);

        return [$value];
    }
}
