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

namespace Mageplaza\Osc\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

/**
 * Class GiftWrap
 * @package Mageplaza\Osc\Model\Total\Creditmemo
 */
class GiftWrap extends AbstractTotal
{
    /**
     * @param Creditmemo $creditmemo
     *
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getOscGiftWrapAmount() < 0.0001) {
            return $this;
        }

        $totalGiftWrapAmount = 0;
        $totalBaseGiftWrapAmount = 0;
        if ($order->getGiftWrapType() == \Mageplaza\Osc\Model\System\Config\Source\Giftwrap::PER_ITEM) {
            foreach ($creditmemo->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy() || ($orderItem->getOscGiftWrapAmount() < 0.001)) {
                    continue;
                }
                $rate = $item->getQty() / $orderItem->getQtyOrdered();

                $totalBaseGiftWrapAmount += $orderItem->getBaseOscGiftWrapAmount() * $rate;
                $totalGiftWrapAmount += $orderItem->getOscGiftWrapAmount() * $rate;
            }
        } elseif ($this->isLast($creditmemo)) {
            $totalGiftWrapAmount = $order->getOscGiftWrapAmount();
            $totalBaseGiftWrapAmount = $order->getBaseOscGiftWrapAmount();
        }

        $creditmemo->setBaseOscGiftWrapAmount($totalBaseGiftWrapAmount);
        $creditmemo->setOscGiftWrapAmount($totalGiftWrapAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalGiftWrapAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $totalBaseGiftWrapAmount);

        return $this;
    }

    /**
     * check credit memo is last or not
     *
     * @param Creditmemo $creditmemo
     *
     * @return boolean
     */
    public function isLast($creditmemo)
    {
        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }

        return true;
    }
}
