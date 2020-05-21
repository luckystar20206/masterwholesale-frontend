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

namespace Mageplaza\Osc\Model\Plugin\Paypal\Model;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class Express
 * @package Mageplaza\Osc\Model\Plugin\Paypal\Model
 */
class Express
{
    /**
     * @param \Magento\Paypal\Model\Express $express
     * @param DataObject $data
     *
     * @return array
     */
    public function beforeAssignData(\Magento\Paypal\Model\Express $express, DataObject $data)
    {
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (is_array($additionalData) && isset($additionalData['extension_attributes'])) {
            unset($additionalData['extension_attributes']);
            $data->setData(PaymentInterface::KEY_ADDITIONAL_DATA, $additionalData);
        }

        return [$data];
    }
}
