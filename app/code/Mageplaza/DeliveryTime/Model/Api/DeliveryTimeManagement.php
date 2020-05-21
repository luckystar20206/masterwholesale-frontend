<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
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

namespace Mageplaza\DeliveryTime\Model\Api;

use Magento\Quote\Api\CartRepositoryInterface;
use Mageplaza\DeliveryTime\Api\Data\DeliveryTimeInterfaceFactory;
use Mageplaza\DeliveryTime\Api\DeliveryTimeManagementInterface;
use Mageplaza\DeliveryTime\Helper\Data;
use Mageplaza\DeliveryTime\Model\Api\Data\DeliveryTime;

/**
 * Class DeliveryTimeManagement
 * @package Mageplaza\DeliveryTime\Model\Api
 */
class DeliveryTimeManagement implements DeliveryTimeManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var DeliveryTimeInterfaceFactory
     */
    private $deliveryTimeFactory;

    /**
     * DeliveryTimeManagement constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param DeliveryTimeInterfaceFactory $deliveryTimeFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        DeliveryTimeInterfaceFactory $deliveryTimeFactory
    ) {
        $this->cartRepository      = $cartRepository;
        $this->deliveryTimeFactory = $deliveryTimeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        $quote = $this->cartRepository->get($cartId);

        $mpDtData = Data::jsonDecode($quote->getData('mp_delivery_information'));

        /** @var DeliveryTime $deliveryTime */
        $deliveryTime = $this->deliveryTimeFactory->create();

        return $deliveryTime->setData($mpDtData);
    }
}
