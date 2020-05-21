<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Neklo\MakeAnOffer\Model\RequestFactory;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;

class Apply extends Action
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var Configurable
     */
    private $configurableResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * Apply constructor.
     * @param Context $context
     * @param RequestFactory $requestFactory
     * @param Configurable $configurableResource
     * @param ProductRepositoryInterface $productRepository
     * @param Cart $cart
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        RequestFactory $requestFactory,
        Configurable $configurableResource,
        ProductRepositoryInterface $productRepository,
        Cart $cart,
        Session $checkoutSession
    ) {
        $this->requestFactory = $requestFactory;
        $this->configurableResource = $configurableResource;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $requestModel = $this->requestFactory->create();
        $requestId = $this->getRequest()->getParam('id');
        $requestModel->load($requestId);

        if ($requestModel->getProductOptions()) {
            $parentIds = $this->configurableResource->getParentIdsByChild($requestModel->getProductId());

            if (isset($parentIds[0])) {
                $product = $this->productRepository->getById($parentIds[0]);
            }
        } else {
            $product = $this->productRepository->getById($requestModel->getProductId());
        }

        $qty = $requestModel->getProductQty();
        $params = [
            'product' => $requestModel->getProductId(),
            'qty' => $qty
        ];

        if ($requestModel->getProductOptions()) {
            $params['super_attribute'] = json_decode($requestModel->getProductOptions(), true);
        }

        try {
            $this->cart->addProduct($product, $params);
            $this->cart->save();
            $this->checkoutSession
                ->getQuote()
                ->setCouponCode($requestModel->getCoupon())
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
        } catch (LocalizedException $e) {
            $this->_redirect($this->_url->getUrl('checkout/cart', ['_secure' => true]));
            return;
        }

        $this->_redirect($this->_url->getUrl('checkout/cart', ['_secure' => true]));
    }
}
