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

namespace Mageplaza\Osc\Model\Plugin\Sales\Order\Address;

use Magento\Sales\Model\Order\Address;
use Mageplaza\Osc\Helper\Data;

/**
 * Class Validator
 * @package Mageplaza\Osc\Model\Plugin\Sales\Order\Address
 */
class Validator
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * Validator constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Address\Validator $subject
     * @param Address $address
     *
     * @return array
     */
    public function beforeValidateForCustomer(Address\Validator $subject, Address $address)
    {
        if ($this->helper->isEnabled()) {
            $address->setShouldIgnoreValidation(true);
        }

        return [$address];
    }
}
