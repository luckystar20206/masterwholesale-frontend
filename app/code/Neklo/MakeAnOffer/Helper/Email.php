<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;

class Email extends AbstractHelper
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $state;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * Email constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $state
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param Config $configHelper
     * @param Data $datahelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $state,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Neklo\MakeAnOffer\Helper\Config $configHelper,
        \Neklo\MakeAnOffer\Helper\Data $datahelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->state = $state;
        $this->productFactory = $productFactory;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->dataHelper = $datahelper;

        parent::__construct($context);
    }

    /**
     * @param $requestItem
     * @param $couponCode
     * @param $reasonCopy
     * @param $discountSum
     * @param $emailType
     * @return $this
     */
    public function sendAcceptEmail($requestItem, $couponCode, $reasonCopy, $discountSum, $emailType)
    {
        $productStoreData = $this->dataHelper->getStoreProductData($requestItem);
        $templateOptions = [
            'area' => Area::AREA_FRONTEND,
            'store' => $requestItem->getStoreId()
        ];

        $couponLifeTime = $this->configHelper->getCouponLifetime($requestItem->getStoreId());

        $price = $this->priceHelper->currency($requestItem->getPrice() - $discountSum, true, false);

        $templateVars = [
            'email'            => $requestItem->getEmail(),
            'product_name'     => $productStoreData['product_name'],
            'qty'              => $requestItem->getProductQty(),
            'coupon_code'      => $couponCode,
            'product_url'      => $productStoreData['product_url'],
            'reason_copy'      => $reasonCopy,
            'price'            => $price,
            'coupon_life_time' => $couponLifeTime,
            'image_url'        => $productStoreData['image_url'],
            'product_options'  => $productStoreData['product_options'],
            'store_name'       => $this->getStoreName($requestItem->getStoreId()),
            'apply_url'        => $productStoreData['apply_url'],
        ];
        $sender = $this->configHelper->getEmailIdentity($requestItem->getStoreId());

        if ($emailType == 'accept') {
            $template = $this->configHelper->getAcceptTemplate($requestItem->getStoreId());
        } else {
            $template = $this->configHelper->getCounterTemplate($requestItem->getStoreId());
        }
        $toEmail = $requestItem->getEmail();

        $this->sendEmail($toEmail, $template, $templateOptions, $templateVars, $sender);

        return $this;
    }

    /**
     * @param $requestItem
     * @param $reasonCopy
     * @return $this
     */
    public function sendDeclineEmail($requestItem, $reasonCopy)
    {
        $productStoreData = $this->dataHelper->getStoreProductData($requestItem);

        $templateOptions = [
            'area' => Area::AREA_FRONTEND,
            'store' => $requestItem->getStoreId()
        ];

        $templateVars = [
            'email'            => $requestItem->getEmail(),
            'product_name'     => $productStoreData['product_name'],
            'qty'              => $requestItem->getProductQty(),
            'product_url'      => $productStoreData['product_url'],
            'reason_copy'      => $reasonCopy,
            'image_url'        => $productStoreData['image_url'],
            'product_options'  => $productStoreData['product_options'],
            'store_name'       => $this->getStoreName($requestItem->getStoreId()),
        ];
        $sender = $this->configHelper->getEmailIdentity($requestItem->getStoreId());
        $template = $this->configHelper->getDeclineTemplate($requestItem->getStoreId());
        $toEmail = $requestItem->getEmail();

        $this->sendEmail($toEmail, $template, $templateOptions, $templateVars, $sender);

        return $this;
    }

    /**
     * @param $requestItem
     * @return $this
     */
    public function notifyCustomer($requestItem)
    {
        $productStoreData = $this->dataHelper->getStoreProductData($requestItem);

        $templateOptions = [
            'area' => Area::AREA_FRONTEND,
            'store' => $requestItem->getStoreId()
        ];

        $price = $this->priceHelper->currency(
            $requestItem->getPrice() - $requestItem->getRequestedSaleAmount(),
            true,
            false
        );

        $templateVars = [
            'email'              => $requestItem->getEmail(),
            'product_name'       => $productStoreData['product_name'],
            'qty'                => $requestItem->getProductQty(),
            'product_url'        => $productStoreData['product_url'],
            'image_url'          => $productStoreData['image_url'],
            'product_options'    => $productStoreData['product_options'],
            'store_name'         => $this->getStoreName($requestItem->getStoreId()),
            'price'              => $price,
        ];

        $template = $this->configHelper->getNotifyCustomerTemplate($requestItem->getStoreId());
        $toEmail = $requestItem->getEmail();

        $sender = $this->configHelper->getEmailIdentity($requestItem->getStoreId());

        $this->sendEmail($toEmail, $template, $templateOptions, $templateVars, $sender);

        return $this;
    }

    /**
     * @param $requestItem
     * @return $this
     */
    public function notifyAdmin($requestItem)
    {
        $emails = $this->configHelper->getNotifyAdminRecipients();
        $toEmails = [];

        if (!is_array($emails)) {
            return $this;
        }

        foreach ($emails as $email) {
            $toEmails[] = $email['email'];
        }

        if (empty($toEmails)) {
            return $this;
        }

        $productStoreData = $this->dataHelper->getStoreProductData($requestItem);

        $templateOptions = [
            'area' => Area::AREA_FRONTEND,
            'store' => $requestItem->getStoreId()
        ];

        $price = $this->priceHelper->currency(
            $requestItem->getPrice() - $requestItem->getRequestedSaleAmount(),
            true,
            false
        );

        $templateVars = [
            'email'              => $requestItem->getEmail(),
            'product_name'       => $productStoreData['product_name'],
            'qty'                => $requestItem->getProductQty(),
            'product_url'        => $productStoreData['product_url'],
            'image_url'          => $productStoreData['image_url'],
            'product_options'    => $productStoreData['product_options'],
            'store_name'         => $this->getStoreName($requestItem->getStoreId()),
            'price'              => $price,

        ];

        $template = $this->configHelper->getNotifyAdminTemplate($requestItem->getStoreId());

        $sender = $this->configHelper->getEmailIdentity($requestItem->getStoreId());

        $this->sendEmail($toEmails, $template, $templateOptions, $templateVars, $sender);

        return $this;
    }

    /**
     * @param $toEmail
     * @param $template
     * @param $templateOptions
     * @param $templateVars
     * @param $sender
     */
    private function sendEmail($toEmail, $template, $templateOptions, $templateVars, $sender)
    {
        $this->state->suspend();
        $transport = $this->transportBuilder->setTemplateIdentifier($template)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($sender);

        if (is_array($toEmail)) {
            foreach ($toEmail as $email) {
                $transport->addTo($email);
            }
        } else {
            $transport->addTo($toEmail);
        }

        $transport = $transport->getTransport();

        $transport->sendMessage();
        $this->state->resume();
    }

    public function getStoreName($storeId)
    {
        $name = (string)$this->scopeConfig->getValue(
            'general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($name) {
            return $name;
        } else {
            return (string)$this->storeManager->getStore($storeId)->getName();
        }
    }
}
