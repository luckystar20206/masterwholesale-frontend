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

namespace Mageplaza\Osc\Observer;

use downloadable_sales_copy_link;
use downloadable_sales_copy_order;
use downloadable_sales_copy_order_item;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\GroupManagementInterface as CustomerGroupManagement;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Downloadable\Model\Link\Purchased\ItemFactory;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CustomerManagement;
use Magento\Store\Model\ScopeInterface;

/**
 * Class QuoteSubmitSuccess
 * @package Mageplaza\Osc\Observer
 */
class QuoteSubmitSuccess implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var Url
     */
    protected $_customerUrl;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var PurchasedFactory
     */
    protected $_purchasedFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var Copy
     */
    protected $_objectCopyService;

    /**
     * @var CollectionFactory
     */
    protected $_itemsFactory;

    /**
     * @var CustomerGroupManagement
     */
    protected $customerGroupManagement;

    /**
     * QuoteSubmitSuccess constructor.
     *
     * @param Session $checkoutSession
     * @param AccountManagementInterface $accountManagement
     * @param Url $customerUrl
     * @param ManagerInterface $messageManager
     * @param CustomerSession $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerManagement $customerManagement
     * @param ScopeConfigInterface $scopeConfig
     * @param PurchasedFactory $purchasedFactory
     * @param ProductFactory $productFactory
     * @param ItemFactory $itemFactory
     * @param CollectionFactory $itemsFactory
     * @param Copy $objectCopyService
     * @param CustomerGroupManagement $customerGroupManagement
     */
    public function __construct(
        Session $checkoutSession,
        AccountManagementInterface $accountManagement,
        Url $customerUrl,
        ManagerInterface $messageManager,
        CustomerSession $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerManagement $customerManagement,
        ScopeConfigInterface $scopeConfig,
        PurchasedFactory $purchasedFactory,
        ProductFactory $productFactory,
        ItemFactory $itemFactory,
        CollectionFactory $itemsFactory,
        Copy $objectCopyService,
        CustomerGroupManagement $customerGroupManagement
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->accountManagement = $accountManagement;
        $this->_customerUrl = $customerUrl;
        $this->messageManager = $messageManager;
        $this->_customerSession = $customerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerManagement = $customerManagement;
        $this->_scopeConfig = $scopeConfig;
        $this->_purchasedFactory = $purchasedFactory;
        $this->_productFactory = $productFactory;
        $this->_itemFactory = $itemFactory;
        $this->_itemsFactory = $itemsFactory;
        $this->_objectCopyService = $objectCopyService;
        $this->customerGroupManagement = $customerGroupManagement;
    }

    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        $oscData = $this->checkoutSession->getOscData();
        if (isset($oscData['register']) && $oscData['register']
            && isset($oscData['password'])
            && $oscData['password']
        ) {
            /* Save customer information for order and save address in address book */
            $this->setCustomerInformation($quote, $order)->setSaveInAddressBook($quote);

            if ($this->checkoutSession->getIsCreatedAccountPaypalExpress()) {
                $customer = $quote->getCustomer();
                $this->checkoutSession->unsIsCreatedAccountPaypalExpress();
            } else {
                $customer = $this->customerManagement->create($order->getId());
            }

            /* Set customer Id for address */
            if ($customer->getId()) {
                $quote->getBillingAddress()->setCustomerId($customer->getId());
                if ($shippingAddress = $quote->getShippingAddress()) {
                    $shippingAddress->setCustomerId($customer->getId());
                }
            }

            if ($customer->getId()
                && $this->accountManagement->getConfirmationStatus($customer->getId())
                   === AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED) {
                $url = $this->_customerUrl->getEmailConfirmationUrl($customer->getEmail());
                $this->messageManager->addSuccessMessage(
                // @codingStandardsIgnoreStart
                    __(
                        'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                        $url
                    )
                // @codingStandardsIgnoreEnd
                );
            } else {
                $this->_customerSession->loginById($customer->getId());
            }

            $isDownloadable = false;
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductType() == Type::TYPE_DOWNLOADABLE) {
                    $isDownloadable = true;
                    break;
                }
            }
            if ($isDownloadable) {
                foreach ($order->getAllItems() as $item) {
                    $this->saveDownloadableOrderItem($item, $order);
                }
            }
        }

        if (isset($oscData['is_subscribed']) && $oscData['is_subscribed']) {
            if (!$this->_customerSession->isLoggedIn()) {
                $subscribedEmail = $quote->getBillingAddress()->getEmail();
            } else {
                $customer = $this->_customerSession->getCustomer();
                $subscribedEmail = $customer->getEmail();
            }

            try {
                $this->subscriberFactory->create()
                    ->subscribe($subscribedEmail);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('There is an error while subscribing for newsletter.'));
            }
        }

        $this->checkoutSession->unsOscData();
    }

    /**
     * @param $orderItem
     * @param $order
     *
     * @return $this
     * @throws Exception
     */
    public function saveDownloadableOrderItem($orderItem, $order)
    {
        if (!$orderItem->getId()) {
            //order not saved in the database
            return $this;
        }
        if ($orderItem->getProductType() != Type::TYPE_DOWNLOADABLE) {
            return $this;
        }
        $product = $orderItem->getProduct();
        $purchasedLink = $this->_createPurchasedModel()->load($orderItem->getId(), 'order_item_id');
        if ($purchasedLink->getId()) {
            return $this;
        }
        if (!$product) {
            $product = $this->_createProductModel()->setStoreId(
                $orderItem->getOrder()->getStoreId()
            )->load(
                $orderItem->getProductId()
            );
        }

        if ($product->getTypeId() == Type::TYPE_DOWNLOADABLE) {
            $links = $product->getTypeInstance()->getLinks($product);
            if ($linkIds = $orderItem->getProductOptionByCode('links')) {
                $linkPurchased = $this->_createPurchasedModel();
                $linkPurchased->setCustomerId(15);
                $this->_objectCopyService->copyFieldsetToTarget(
                    downloadable_sales_copy_order::class,
                    'to_downloadable',
                    $orderItem->getOrder(),
                    $linkPurchased
                );
                $this->_objectCopyService->copyFieldsetToTarget(
                    downloadable_sales_copy_order_item::class,
                    'to_downloadable',
                    $orderItem,
                    $linkPurchased
                );
                $linkSectionTitle = $product->getLinksTitle() ? $product
                    ->getLinksTitle() : $this
                    ->_scopeConfig
                    ->getValue(
                        Link::XML_PATH_LINKS_TITLE,
                        ScopeInterface::SCOPE_STORE
                    );
                $linkPurchased->setLinkSectionTitle($linkSectionTitle)->save();
                foreach ($linkIds as $linkId) {
                    if (isset($links[$linkId])) {
                        $linkPurchasedItem = $this->_createPurchasedItemModel()->setPurchasedId(
                            $linkPurchased->getId()
                        )->setOrderItemId(
                            $orderItem->getId()
                        );

                        $this->_objectCopyService->copyFieldsetToTarget(
                            downloadable_sales_copy_link::class,
                            'to_purchased',
                            $links[$linkId],
                            $linkPurchasedItem
                        );
                        $linkHash = strtr(
                            base64_encode(
                                microtime() . $linkPurchased->getId() . $orderItem->getId() . $product->getId()
                            ),
                            '+/=',
                            '-_,'
                        );
                        $numberOfDownloads = $links[$linkId]->getNumberOfDownloads() * $orderItem->getQtyOrdered();

                        switch ($order->getState()) {
                            case Order::STATE_PENDING_PAYMENT:
                                $status = Item::LINK_STATUS_PENDING_PAYMENT;
                                break;
                            case Order::STATE_PAYMENT_REVIEW:
                                $status = Item::LINK_STATUS_PAYMENT_REVIEW;
                                break;
                            case Order::STATE_COMPLETE:
                                $status = Item::LINK_STATUS_AVAILABLE;
                                break;
                            default:
                                $status = Item::LINK_STATUS_PENDING;
                        }

                        $linkPurchasedItem->setLinkHash(
                            $linkHash
                        )->setNumberOfDownloadsBought(
                            $numberOfDownloads
                        )->setStatus(
                            $status
                        )->setCreatedAt(
                            $orderItem->getCreatedAt()
                        )->setUpdatedAt(
                            $orderItem->getUpdatedAt()
                        )->save();
                    }
                }
            }
        }
    }

    /**
     * @return Purchased
     */
    protected function _createPurchasedModel()
    {
        return $this->_purchasedFactory->create();
    }

    /**
     * @return Product
     */
    protected function _createProductModel()
    {
        return $this->_productFactory->create();
    }

    /**
     * @return Item
     */
    protected function _createPurchasedItemModel()
    {
        return $this->_itemFactory->create();
    }

    /**
     * @return Collection
     */
    protected function _createItemsCollection()
    {
        return $this->_itemsFactory->create();
    }

    /**
     * Set the customer information to the order when choosing to create an account
     *
     * @param Quote $quote
     * @param Order $order
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setCustomerInformation($quote, $order)
    {
        $billingAddress = $quote->getBillingAddress();

        $order->setCustomerFirstname(
            $billingAddress->getFirstname()
        )->setCustomerLastname(
            $billingAddress->getLastname()
        )->setCustomerMiddlename(
            $billingAddress->getMiddlename()
        )->setCustomerGroupId(
            $this->customerGroupManagement->getDefaultGroup($quote->getStoreId())->getId()
        );

        return $this;
    }

    /**
     * @param Quote $quote
     */
    public function setSaveInAddressBook($quote)
    {
        $quote->getBillingAddress()->setSaveInAddressBook(1);
        $quote->getShippingAddress()->setSaveInAddressBook(1);
        $quote->save();
    }
}
