<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Statistic extends AbstractHelper
{
    /**
     * @var \Neklo\MakeAnOffer\Model\StatisticFactory
     */
    private $statisticFactory;

    /**
     * Statistic constructor.
     * @param Context $context
     * @param \Neklo\MakeAnOffer\Model\StatisticFactory $statisticFactory
     */
    public function __construct(
        Context $context,
        \Neklo\MakeAnOffer\Model\StatisticFactory $statisticFactory
    ) {
        $this->statisticFactory = $statisticFactory;
        parent::__construct($context);
    }

    /**
     * @param $sku
     */
    public function updateAcceptedQty($sku)
    {
        $statisticItem = $this->statisticFactory->create()->load($sku, 'product_sku');
        $statisticItem->setAcceptedQty($statisticItem->getAcceptedQty() + 1);
        $statisticItem->save();
    }

    /**
     * @param $sku
     */
    public function updateDeclinedQty($sku)
    {
        $statisticItem = $this->statisticFactory->create()->load($sku, 'product_sku');
        $statisticItem->setDeclinedQty($statisticItem->getDeclinedQty() + 1);
        $statisticItem->save();
    }

    /**
     * @param $requestId
     * @param $sku
     */
    public function addTotalRequests($requestId, $sku)
    {
        $statisticItem = $this->statisticFactory->create()->load($sku, 'product_sku');
        if (!$statisticItem->getId()) {
            $statisticItem->setProductId($requestId);
            $statisticItem->setProductSku($sku);
            $statisticItem->setTotalRequests(1);
            $statisticItem->save();
        } else {
            $statisticItem->setTotalRequests($statisticItem->getTotalRequests() + 1);
            $statisticItem->save();
        }
        $statisticItem->setTotalRequests($statisticItem->getTotalRequests() + 1);
    }

    /**
     * @param $sku
     * @param $appliedCouponAmount
     */
    public function updateTotalDiscount($sku, $appliedCouponAmount)
    {
        $statisticItem = $this->statisticFactory->create()->load($sku, 'product_sku');
        $statisticItem->setTotalDiscount((int)$statisticItem->getTotalDiscount() + $appliedCouponAmount);
        $statisticItem->setOrderedQty((int)$statisticItem->getOrderedQty() + 1);
        $statisticItem->save();
    }
}
