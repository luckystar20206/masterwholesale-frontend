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

namespace Mageplaza\Osc\Model\Plugin\Customer;

use Magento\Customer\Api\Data\AddressInterface;

/**
 * Class Address
 * @package Mageplaza\Osc\Model\Plugin\Customer
 */
class Address
{
    /**
     * @param \Magento\Customer\Model\Address $subject
     * @param \Magento\Customer\Model\Address $result
     *
     * @return \Magento\Customer\Model\Address
     */
    public function afterUpdateData(\Magento\Customer\Model\Address $subject, $result)
    {
        $result->setShouldIgnoreValidation(true);

        return $result;
    }

    /**
     * @param \Magento\Customer\Model\Address $subject
     * @param AddressInterface $address
     *
     * @return mixed
     */
    public function beforeUpdateData(\Magento\Customer\Model\Address $subject, AddressInterface $address)
    {
        $customAttributes = $address->getCustomAttributes();
        foreach ($customAttributes as $key => $attribute) {
            if (($key === 'mposc_field_1' || $key === 'mposc_field_2' || $key === 'mposc_field_3') && !$attribute) {
                $address->setCustomAttribute($key, '');
            }
        }

        return [$address];
    }
}
