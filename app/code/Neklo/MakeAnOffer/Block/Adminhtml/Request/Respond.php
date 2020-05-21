<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Block\Adminhtml\Request;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Data\Form\FormKey;
use Neklo\MakeAnOffer\Model\Source\Status;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class Respond extends \Magento\Framework\View\Element\Template
{
    const REGISTRY_REQUEST_KEY = 'current_request';

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * Respond constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UrlInterface $backendUrl,
        FormKey $formKey,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->backendUrl = $backendUrl;
        $this->formKey = $formKey;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @return \Neklo\MakeAnOffer\Model\Request
     */
    public function getItem()
    {
        return $this->registry->registry(self::REGISTRY_REQUEST_KEY);
    }

    /**
     * Get Product Edit Url
     *
     * @return string
     */
    public function getProductEditUrl()
    {
        $productId = $this->getItem()->getProductId();
        return  $this->backendUrl->getUrl('catalog/product/edit', ['id' => $productId]);
    }

    /**
     * Get url for back button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->backendUrl->getUrl('neklo_makeanoffer/request/index');
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Get Accept action url
     *
     * @return string
     */
    public function getAcceptUrl()
    {
        return $this->backendUrl->getUrl('neklo_makeanoffer/request/accept');
    }

    /**
     * Get Decline action url
     *
     * @return string
     */
    public function getDeclineUrl()
    {
        return $this->backendUrl->getUrl('neklo_makeanoffer/request/decline');
    }

    /**
     * Change status form New to Pending
     */
    public function changeStatus()
    {
        $request = $this->getItem();
        if (!$request->getId()) {
            return false;
        }

        $request->setStatus(Status::PENDING_REQUEST_STATUS);
        $request->save();

        return true;
    }

    public function getRenderedPrice($price, $format = true)
    {
        return $this->priceHelper->currency($price, $format, false);
    }

    public function getCustomerEditUrl()
    {
        $customerId = $this->getItem()->getCustomerId();

        if (!$customerId || $customerId === null) {
            return __('(Guest)');
        }

        return $this->backendUrl->getUrl('customer/index/edit', ['id' => $customerId]);
    }
}
