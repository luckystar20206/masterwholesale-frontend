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

namespace Mageplaza\Osc\Model\Total\Quote;

use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Mageplaza\Osc\Helper\Data as OscHelper;
use Mageplaza\Osc\Model\System\Config\Source\Giftwrap as SourceGiftwrap;

/**
 * Class GiftWrap
 * @package Mageplaza\Osc\Model\Total\Quote
 */
class GiftWrap extends AbstractTotal
{
    /**
     * @var OscHelper
     */
    protected $_oscHelper;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var
     */
    protected $_baseGiftWrapAmount;

    /**
     * GiftWrap constructor.
     *
     * @param Session $checkoutSession
     * @param OscHelper $oscHelper
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Session $checkoutSession,
        OscHelper $oscHelper,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_oscHelper = $oscHelper;
        $this->priceCurrency = $priceCurrency;

        $this->setCode('osc_gift_wrap');
    }

    /**
     * Collect gift wrap totals
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if ($this->_oscHelper->isDisabledGiftWrap($quote->getStoreId())
            || ($shippingAssignment->getShipping()->getAddress()->getAddressType() !== Address::TYPE_SHIPPING)
            || !$quote->getShippingAddress()->getUsedGiftWrap()
        ) {
            return $this;
        }

        $baseOscGiftWrapAmount = $this->calculateGiftWrapAmount($quote);
        $oscGiftWrapAmount = $this->priceCurrency->convert($baseOscGiftWrapAmount, $quote->getStore());

        $this->_addAmount($oscGiftWrapAmount);
        $this->_addBaseAmount($baseOscGiftWrapAmount);

        return $this;
    }

    /**
     * Assign gift wrap amount and label to address object
     *
     * @param Quote $quote
     * @param Address\Total $total
     *
     * @return array
     */
    public function fetch(Quote $quote, Total $total)
    {
        if ($this->_oscHelper->isDisabledGiftWrap($quote->getStoreId()) || !$quote->getShippingAddress()->getUsedGiftWrap()) {
            return [];
        }
        $amount = $total->getOscGiftWrapAmount();

        $baseInitAmount = $this->calculateGiftWrapAmount($quote);
        $initAmount = $this->priceCurrency->convert($baseInitAmount, $quote->getStore());

        return [
            'code'             => $this->getCode(),
            'title'            => __('Gift Wrap'),
            'value'            => $amount,
            'gift_wrap_amount' => $initAmount
        ];
    }

    /**
     * @param $quote
     *
     * @return int|mixed
     */
    public function calculateGiftWrapAmount($quote)
    {
        if ($this->_baseGiftWrapAmount == null) {
            $baseOscGiftWrapAmount = $this->_oscHelper->getOrderGiftwrapAmount($quote->getStoreId());
            if ($baseOscGiftWrapAmount == 0) {
                return 0;
            }

            $giftWrapType = $this->_oscHelper->getGiftWrapType($quote->getStoreId());
            if ($giftWrapType == SourceGiftwrap::PER_ITEM) {
                $giftWrapBaseAmount = $baseOscGiftWrapAmount;
                $baseOscGiftWrapAmount = 0;
                foreach ($quote->getAllVisibleItems() as $item) {
                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        continue;
                    }
                    $baseItemGiftWrapAmount = $giftWrapBaseAmount * $item->getQty();
                    $item->setBaseOscGiftWrapAmount($baseItemGiftWrapAmount);
                    $item->setOscGiftWrapAmount($this->priceCurrency->convert(
                        $baseItemGiftWrapAmount,
                        $quote->getStore()
                    ));

                    $baseOscGiftWrapAmount += $baseItemGiftWrapAmount;
                }
            }
            $quote->getShippingAddress()->setGiftWrapType($giftWrapType);

            $this->_baseGiftWrapAmount = $baseOscGiftWrapAmount;
        }

        return $this->_baseGiftWrapAmount;
    }
}
