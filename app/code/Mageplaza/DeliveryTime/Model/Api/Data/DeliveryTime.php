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
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DeliveryTime\Model\Api\Data;

use Magento\Framework\Model\AbstractExtensibleModel;
use Mageplaza\DeliveryTime\Api\Data\DeliveryTimeInterface;

/**
 * Class DeliveryTime
 * @package Mageplaza\DeliveryTime\Model\Api\Data
 */
class DeliveryTime extends AbstractExtensibleModel implements DeliveryTimeInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDeliveryDate()
    {
        return $this->getData('deliveryDate');
    }

    /**
     * {@inheritDoc}
     */
    public function setDeliveryDate($value)
    {
        return $this->setData('deliveryDate', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getDeliveryTime()
    {
        return $this->getData('deliveryTime');
    }

    /**
     * {@inheritDoc}
     */
    public function setDeliveryTime($value)
    {
        return $this->setData('deliveryTime', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getHouseSecurityCode()
    {
        return $this->getData('houseSecurityCode');
    }

    /**
     * {@inheritDoc}
     */
    public function setHouseSecurityCode($value)
    {
        return $this->setData('houseSecurityCode', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getDeliveryComment()
    {
        return $this->getData('deliveryComment');
    }

    /**
     * {@inheritDoc}
     */
    public function setDeliveryComment($value)
    {
        return $this->setData('deliveryComment', $value);
    }
}
