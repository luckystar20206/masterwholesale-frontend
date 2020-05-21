<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Order;

/**
 * Class ShippingAddress
 * @package Ess\M2ePro\Model\Ebay\Order
 */
class ShippingAddress extends \Ess\M2ePro\Model\Order\ShippingAddress
{
    //########################################

    /**
     * @return array
     */
    public function getRawData()
    {
        return [
            'buyer_name'     => $this->order->getChildObject()->getBuyerName(),
            'recipient_name' => $this->order->getChildObject()->getBuyerName(),
            'email'          => $this->getBuyerEmail(),
            'country_id'     => $this->getData('country_code'),
            'region'         => $this->getData('state'),
            'city'           => $this->getData('city'),
            'postcode'       => $this->getPostalCode(),
            'telephone'      => $this->getPhone(),
            'company'        => $this->getData('company'),
            'street'         => array_filter($this->getData('street'))
        ];
    }

    private function getBuyerEmail()
    {
        $email = $this->order->getChildObject()->getData('buyer_email');

        if (stripos($email, 'Invalid Request') !== false || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = str_replace(' ', '-', strtolower($this->order->getChildObject()->getBuyerUserId()));
            $email .= \Ess\M2ePro\Model\Magento\Customer::FAKE_EMAIL_POSTFIX;
        }

        return $email;
    }

    private function getPostalCode()
    {
        $postalCode = $this->getData('postal_code');

        if (stripos($postalCode, 'Invalid Request') !== false || $postalCode == '') {
            $postalCode = '0000';
        }

        return $postalCode;
    }

    private function getPhone()
    {
        $phone = $this->getData('phone');

        if (stripos($phone, 'Invalid Request') !== false || $phone == '') {
            $phone = '0000000000';
        }

        return $phone;
    }

    //########################################
}
