<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\Osc\Model\Plugin\Quote\Address;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomAttributeList
 * @package Mageplaza\Osc\Model\Plugin\Quote\Address
 */
class CustomAttributeList
{
    /**
     * @var \Mageplaza\Osc\Model\CustomAttributeList
     */
    private $customAttributeList;

    /**
     * CustomAttributeList constructor.
     *
     * @param \Mageplaza\Osc\Model\CustomAttributeList $customAttributeList
     */
    public function __construct(
        \Mageplaza\Osc\Model\CustomAttributeList $customAttributeList
    ) {
        $this->customAttributeList = $customAttributeList;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\CustomAttributeList $subject
     * @param array $result
     *
     * @return array
     * @throws LocalizedException
     */
    public function afterGetAttributes(
        \Magento\Quote\Model\Quote\Address\CustomAttributeList $subject,
        $result
    ) {
        return array_merge($result, $this->customAttributeList->getAttributes());
    }
}
