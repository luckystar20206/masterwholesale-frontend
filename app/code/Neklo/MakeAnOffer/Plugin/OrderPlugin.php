<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Plugin;

use Neklo\MakeAnOffer\Model\Source\Status;
use Neklo\MakeAnOffer\Model\ResourceModel\Request\CollectionFactory;
use Neklo\MakeAnOffer\Helper\Statistic;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Service\OrderService;

class OrderPlugin
{

    /**
     * @var Statistic
     */
    private $statisticHelper;

    /**
     * @var CollectionFactory
     */
    private $requestCollection;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * OrderPlugin constructor.
     *
     * @param DateTime          $date
     * @param Statistic         $statisticHelper
     * @param CollectionFactory $requestCollection
     */
    public function __construct(
        DateTime $date,
        Statistic $statisticHelper,
        CollectionFactory $requestCollection
    ) {
        $this->date = $date;
        $this->statisticHelper = $statisticHelper;
        $this->requestCollection = $requestCollection;
    }

    /**
     * @param OrderService         $subject
     * @param                      $order
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(OrderService $subject, $order)
    {
        if ($order->getCouponCode() === null) {
            return $order;
        }
        $requestItem = $this->requestCollection->create()
            ->addFieldToFilter('main_table.coupon', ['eq' => $order->getCouponCode()])
            ->getFirstItem();

        if (!$requestItem->getId()) {
            return $order;
        }

        $item = $order->getItemsCollection()
            ->addFieldToFilter('sku', ['eq' => $requestItem->getProductSku()])
            ->getFirstItem();

        $soldPrice = $item->getPrice() * $requestItem->getProductQty();
        $soldPrice -= $requestItem->getAppliedCouponAmount();

        $orderId = $order->getEntityId();

        $requestItem->setOrderId($orderId);
        $requestItem->setSoldPrice($soldPrice);
        $requestItem->setStatus(Status::COMPLETED_REQUEST_STATUS);
        $requestItem->save();

        $this->statisticHelper->updateTotalDiscount(
            $requestItem->getProductSku(),
            $requestItem->getAppliedCouponAmount()
        );

        return $order;
    }
}
