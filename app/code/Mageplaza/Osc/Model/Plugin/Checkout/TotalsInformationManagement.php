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

namespace Mageplaza\Osc\Model\Plugin\Checkout;

use Closure;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Quote;

/**
 * Class TotalsInformationManagement
 * @package Mageplaza\Osc\Model\Plugin\Checkout
 */
class TotalsInformationManagement
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param CartTotalRepositoryInterface $cartTotalRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CartTotalRepositoryInterface $cartTotalRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->cartTotalRepository = $cartTotalRepository;
    }

    /**
     * @param \Magento\Checkout\Model\TotalsInformationManagement $subject
     * @param Closure $proceed
     * @param $cartId
     * @param TotalsInformationInterface $addressInformation
     *
     * @return TotalsInterface
     * @throws NoSuchEntityException
     */
    public function aroundCalculate(
        \Magento\Checkout\Model\TotalsInformationManagement $subject,
        Closure $proceed,
        $cartId,
        TotalsInformationInterface $addressInformation
    ) {
        $result = $proceed($cartId, $addressInformation);

        /* @var Quote $quote */
        $quote = $this->quoteRepository->get($cartId);

        $extensionAttributes = $quote->getExtensionAttributes();
        if ($extensionAttributes && !$quote->isVirtual() && $extensionAttributes->getShippingAssignments()) {
            /** @var ShippingAssignmentInterface[] $shippingAssignments */
            $shippingAssignments = $extensionAttributes->getShippingAssignments();

            if (count($shippingAssignments)) {
                $shippingAssignments[0]->getShipping()->setMethod($quote->getShippingAddress()->getShippingMethod());
            }

            $this->quoteRepository->save($quote);
        }

        return $result;
    }
}
