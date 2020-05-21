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

namespace Mageplaza\DeliveryTime\Api;

/**
 * Interface DeliveryTimeManagementInterface
 *
 * @package Mageplaza\DeliveryTime\Api
 */
interface DeliveryTimeManagementInterface
{
    /**
     * @param string $cartId
     *
     * @return \Mageplaza\DeliveryTime\Api\Data\DeliveryTimeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($cartId);
}
