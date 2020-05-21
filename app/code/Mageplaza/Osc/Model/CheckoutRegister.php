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

namespace Mageplaza\Osc\Model;

use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Address;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\Quote;
use Mageplaza\Osc\Helper\Data;

/**
 * Class CheckoutRegister
 * @package Mageplaza\Osc\Model
 */
class CheckoutRegister
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Copy
     */
    protected $_objectCopyService;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var bool
     */
    protected $_isCheckedRegister = false;

    /**
     * @var Data
     */
    protected $_oscHelper;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * CheckoutRegister constructor.
     *
     * @param Session $checkoutSession
     * @param Copy $objectCopyService
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountManagementInterface $accountManagement
     * @param CustomerManagement $customerManagement
     * @param Data $oscHelper
     * @param Encryptor $encryptor
     */
    public function __construct(
        Session $checkoutSession,
        Copy $objectCopyService,
        DataObjectHelper $dataObjectHelper,
        AccountManagementInterface $accountManagement,
        CustomerManagement $customerManagement,
        Data $oscHelper,
        Encryptor $encryptor
    ) {
        $this->checkoutSession    = $checkoutSession;
        $this->_objectCopyService = $objectCopyService;
        $this->dataObjectHelper   = $dataObjectHelper;
        $this->accountManagement  = $accountManagement;
        $this->customerManagement = $customerManagement;
        $this->_oscHelper         = $oscHelper;
        $this->encryptor          = $encryptor;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function checkRegisterNewCustomer()
    {
        if ($this->isCheckedRegister()) {
            return $this;
        }

        $this->setIsCheckedRegister(true);

        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        /** Validate address */
        $this->validateAddressBeforeSubmit($quote);

        /** One step check out additional data */
        $oscData = $this->checkoutSession->getOscData();

        /** Create account when checkout */
        if (!empty($oscData['register']) && !empty($oscData['password'])) {
            $this->checkoutSession->setIsCreatedAccountPaypalExpress(true);
            $quote->setCheckoutMethod(Onepage::METHOD_REGISTER)
                ->setCustomerIsGuest(false)
                ->setCustomerGroupId(null)
                ->setPasswordHash(
                    $this->createPasswordHash($oscData['password'])
                );

            $this->_prepareNewCustomerQuote($quote, $oscData);
        }

        return $this;
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @param Quote $quote
     * @param array $oscData
     *
     * @return void
     */
    public function _prepareNewCustomerQuote(Quote $quote, $oscData)
    {
        $billing  = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer  = $quote->getCustomer();
        $dataArray = $billing->getData();
        if (isset($oscData['customerAttributes']) && $oscData['customerAttributes']) {
            $dataArray = array_merge($dataArray, $oscData['customerAttributes']);
        }
        $this->dataObjectHelper->populateWithArray($customer, $dataArray, CustomerInterface::class);
        $customer->setEmail($quote->getCustomerEmail());
        $quote->setCustomer($customer);

        /** Create customer */
        $this->customerManagement->populateCustomerInfo($quote);

        $this->_oscHelper->setFlagOscMethodRegister(true);

        /** Init customer address */
        /** @var Address $customerBillingData */
        $customerBillingData = $billing->exportCustomerAddress();
        $customerBillingData->setIsDefaultBilling(true)
            ->setData('should_ignore_validation', true);

        if ($shipping) {
            if (isset($oscData['same_as_shipping']) && $oscData['same_as_shipping']) {
                $shipping->setCustomerAddressData($customerBillingData);
                $customerBillingData->setIsDefaultShipping(true);
            } else {
                /** @var Address $customerShippingData */
                $customerShippingData = $shipping->exportCustomerAddress();
                $customerShippingData->setIsDefaultShipping(true)
                    ->setData('should_ignore_validation', true);
                $shipping->setCustomerAddressData($customerShippingData);
                // Add shipping address to quote since customer Data Object does not hold address information
                $quote->addCustomerAddress($customerShippingData);
            }
        } else {
            $customerBillingData->setIsDefaultShipping(true);
        }
        $billing->setCustomerAddressData($customerBillingData);
        // Add billing address to quote since customer Data Object does not hold address information
        $quote->addCustomerAddress($customerBillingData);

        // If customer is created, set customerId for address to avoid create more address when checkout
        if ($customerId = $quote->getCustomerId()) {
            $quote->getBillingAddress()->setCustomerId($customerId);
            if (!$quote->isVirtual()) {
                $quote->getShippingAddress()->setCustomerId($customerId);
            }
        }
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     */
    public function validateAddressBeforeSubmit(Quote $quote)
    {
        /** Remove address validation */
        if (!$quote->isVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
        $quote->getBillingAddress()->setShouldIgnoreValidation(true);

        // TODO : Validate address (depend on field require, show on osc or not)

        return $this;
    }

    /**
     * @return bool
     */
    public function isCheckedRegister()
    {
        return $this->_isCheckedRegister;
    }

    /**
     * @param bool $isCheckedRegister
     */
    public function setIsCheckedRegister($isCheckedRegister)
    {
        $this->_isCheckedRegister = $isCheckedRegister;
    }

    /**
     * Create a hash for the given password
     *
     * @param string $password
     *
     * @return string
     */
    private function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }
}
