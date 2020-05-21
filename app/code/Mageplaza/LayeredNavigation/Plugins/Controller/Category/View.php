<?php

namespace Mageplaza\LayeredNavigation\Plugins\Controller\Category;

class View
{
	protected $_jsonHelper;
	protected $_moduleHelper;

	public function __construct(
		\Magento\Framework\Json\Helper\Data $jsonHelper,
		\Mageplaza\LayeredNavigation\Helper\Data $moduleHelper
	){
		$this->_jsonHelper = $jsonHelper;
		$this->_moduleHelper = $moduleHelper;
	}
    public function afterExecute(\Magento\Catalog\Controller\Category\View $action, $page)
	{
		if($this->_moduleHelper->isEnabled() && $action->getRequest()->getParam('isAjax')){
			$navigation = $page->getLayout()->getBlock('catalog.leftnav');
			$products = $page->getLayout()->getBlock('category.products');
			$result = ['products' => $products->toHtml(), 'navigation' => $navigation->toHtml()];
			$action->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
		} else {
			return $page;
		}
    }
}
