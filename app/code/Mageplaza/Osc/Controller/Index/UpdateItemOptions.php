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

namespace Mageplaza\Osc\Controller\Index;

use Exception;
use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Zend_Filter_LocalizedToNormalized;

/**
 * Class UpdateItemOptions
 * @package Mageplaza\Osc\Controller\Index
 */
class UpdateItemOptions extends Cart
{
    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpdateItemOptions constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ResolverInterface $resolver
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerCart $cart,
        ResolverInterface $resolver,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        $this->resolver          = $resolver;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger            = $logger;

        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
    }

    /**
     * Update product configuration for a cart item
     *
     * @return Json
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $id     = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = [];
        }

        try {
            if (isset($params['qty'])) {
                $filter        = new Zend_Filter_LocalizedToNormalized([
                    'locale' => $this->resolver->getLocale()
                ]);
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $this->cart->getQuote()->getItemById($id);

            if (!$quoteItem) {
                return $resultJson->setData(['error' => __("The quote item isn't found. Verify the item and try again.")]);
            }

            $item = $this->cart->updateItem($id, new DataObject($params));
            if (is_string($item)) {
                return $resultJson->setData(['error' => $item]);
            }
            if ($item->getHasError()) {
                return $resultJson->setData(['error' => $item->getMessage()]);
            }

            $this->cart->save();

            $this->_eventManager->dispatch(
                'checkout_cart_update_item_complete',
                ['item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );
        } catch (LocalizedException $e) {
            return $resultJson->setData(['error' => $e->getMessage()]);
        } catch (Exception $e) {
            $this->logger->critical($e);

            return $resultJson->setData(['error' => __("We can't update the item right now.")]);
        }

        return $resultJson->setData(['success' => true]);
    }
}
