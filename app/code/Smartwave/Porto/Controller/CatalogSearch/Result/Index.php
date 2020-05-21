<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Smartwave\Porto\Controller\CatalogSearch\Result;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\CatalogSearch\Controller\Result\Index
{
    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;
	
	protected $_scopeConfig;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
		$this->_catalogSession = $catalogSession;
		$this->_storeManager = $storeManager;
		$this->_queryFactory = $queryFactory;
		$this->layerResolver = $layerResolver;
        parent::__construct($context, $catalogSession, $storeManager, $queryFactory, $layerResolver);
		$this->resultFactory = $context->getResultFactory();
		$this->_scopeConfig = $scopeConfig;
    }

    /**
     * Display search result
     *
     * @return void
     */
    public function execute()
    {
		$this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
		/* @var $query \Magento\Search\Model\Query */
		$query = $this->_queryFactory->get();

		$query->setStoreId($this->_storeManager->getStore()->getId());

		if ($query->getQueryText() != '') {
			if ($this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->isMinQueryLength()) {
				$query->setId(0)->setIsActive(1)->setIsProcessed(1);
			} else {
				$query->saveIncrementalPopularity();

				if ($query->getRedirect()) {
					$this->getResponse()->setRedirect($query->getRedirect());
					return;
				}
			}

			$this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->checkNotes();
			
            $full_width = $this->_scopeConfig->getValue('porto_settings/general/layout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
            $additional_class = '';
            if(isset($full_width) && $full_width == 'full_width'){
                $additional_class = 'layout-fullwidth';
            }
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->addBodyClass($additional_class);
			$panelLayout = $this->_scopeConfig->getValue('porto_settings/category/page_layout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
			if($panelLayout!=''){
                $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
				$resultPage->getConfig()->setPageLayout($panelLayout);
				return $resultPage;
			}else{
				$this->_view->loadLayout();
				$this->_view->renderLayout();
			}
			
		} else {
			$this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
		}
    }
}
