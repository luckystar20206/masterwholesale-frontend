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

namespace Mageplaza\Osc\Block\Order\View;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order\Address;

/**
 * Class CustomField
 * @package Mageplaza\Osc\Block\Order\View
 */
class CustomField extends AbstractView
{
    /**
     * @return array
     */
    public function getAddressData()
    {
        if (!$order = $this->getOrder()) {
            return [];
        }

        $result = [];

        if ($billing = $this->getFieldData($order->getBillingAddress())) {
            $result['billing'] = [
                'label' => __('Billing Address'),
                'value' => $billing,
            ];
        }

        if ($shipping = $this->getFieldData($order->getShippingAddress())) {
            $result['shipping'] = [
                'label' => __('Shipping Address'),
                'value' => $shipping,
            ];
        }

        return $result;
    }

    /**
     * @param Address|OrderAddressInterface $address
     *
     * @return array
     */
    private function getFieldData($address)
    {
        if (!$address) {
            return [];
        }

        $result = [];
        for ($i = 1; $i <= 3; $i++) {
            if ($value = $address->getData('mposc_field_' . $i)) {
                if ($i === 3) {
                    $value = date('M d, Y', strtotime($value));
                }

                $result[] = [
                    'label' => $this->helper->getCustomFieldLabel($i),
                    'value' => $value
                ];
            }
        }

        return $result;
    }
}
