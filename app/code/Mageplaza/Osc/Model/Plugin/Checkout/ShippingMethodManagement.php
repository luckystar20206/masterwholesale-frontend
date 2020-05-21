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
use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\Quote;

/**
 * Class ShippingMethodManagement
 * @package Mageplaza\Osc\Model\Plugin\Checkout
 */
class ShippingMethodManagement
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Customer Address repository
     *
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param Closure $proceed
     * @param $cartId
     * @param EstimateAddressInterface $address
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundEstimateByAddress(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        Closure $proceed,
        $cartId,
        EstimateAddressInterface $address
    ) {
        $this->saveAddress($cartId, $address);

        return $proceed($cartId, $address);
    }

    /**
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param Closure $proceed
     * @param $cartId
     * @param AddressInterface $address
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundEstimateByExtendedAddress(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        Closure $proceed,
        $cartId,
        AddressInterface $address
    ) {
        $this->saveAddress($cartId, $address);

        return $proceed($cartId, $address);
    }

    /**
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param Closure $proceed
     * @param $cartId
     * @param $addressId
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundEstimateByAddressId(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        Closure $proceed,
        $cartId,
        $addressId
    ) {
        $address = $this->addressRepository->getById($addressId);
        $this->saveAddress($cartId, $address);

        return $proceed($cartId, $addressId);
    }

    /**
     * @param $cartId
     * @param EstimateAddressInterface|AddressInterface|\Magento\Customer\Api\Data\AddressInterface $address
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    private function saveAddress($cartId, $address)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$quote->isVirtual()) {
            $addressData = [
                AddressInterface::KEY_COUNTRY_ID      => $address->getCountryId(),
                AddressInterface::KEY_POSTCODE        => $address->getPostcode(),
                AddressInterface::KEY_REGION_ID       => $address->getRegionId(),
                AddressInterface::KEY_REGION          => $address->getRegion(),
                AddressInterface::KEY_STREET          => $address->getStreet(),
                AddressInterface::KEY_CITY            => $address->getCity(),
                AddressInterface::CUSTOMER_ADDRESS_ID => $address->getId()
            ];

            $shippingAddress = $quote->getShippingAddress();
            try {
                $shippingAddress->addData($addressData)
                    ->save();
                $this->quoteRepository->save($quote);
            } catch (Exception $e) {
                return $this;
            }
        }

        return $this;
    }
}
