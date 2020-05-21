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

namespace Mageplaza\Osc\Model\Plugin\Customer\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Class ConvertQuoteAddressToCustomerAddress
 * @package Mageplaza\Osc\Model\Plugin\Customer\Address
 */
class ConvertQuoteAddressToCustomerAddress
{
    /**
     * @param Address $quoteAddress
     * @param AddressInterface $customerAddress
     *
     * @return AddressInterface
     */
    public function afterExportCustomerAddress(
        Address $quoteAddress,
        AddressInterface $customerAddress
    ) {
        for ($i = 1; $i <= 3; $i++) {
            $key = 'mposc_field_' . $i;
            if ($value = $quoteAddress->getData($key)) {
                $customerAddress->setCustomAttribute($key, $value);
            }
        }

        return $customerAddress;
    }
}
