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

namespace Mageplaza\Osc\Controller\Add;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Controller\Cart;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Osc\Helper\Data;

/**
 * Class Index
 * @package Mageplaza\Osc\Controller\Add
 */
class Index extends Cart
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $productId         = $this->getRequest()->getParam('id') ?: 11;
        $storeId           = $this->_objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $cart              = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
        $oscHelper         = $this->_objectManager->get(Data::class);
        $product           = $productRepository->getById($productId, false, $storeId);

        $cart->addProduct($product, []);
        $cart->save();

        return $this->goBack($this->_url->getUrl($oscHelper->getOscRoute()));
    }

    /**
     * @param null $backUrl
     * @param null $product
     *
     * @return Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } elseif ($product && !$product->getIsSalable()) {
            $result['product'] = [
                'statusText' => __('Out of stock')
            ];
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}
