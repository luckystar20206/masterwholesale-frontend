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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Plugin\Controller\Category;

/**
 * Class View
 * @package Mageplaza\LayeredNavigation\Controller\Plugin\Category
 */
class View
{
	/** @var \Magento\Framework\Json\Helper\Data */
	protected $_jsonHelper;

	/** @var \Mageplaza\LayeredNavigation\Helper\Data */
	protected $_moduleHelper;

	/**
	 * @param \Magento\Framework\Json\Helper\Data $jsonHelper
	 * @param \Mageplaza\LayeredNavigation\Helper\Data $moduleHelper
	 */
	public function __construct(
		\Magento\Framework\Json\Helper\Data $jsonHelper,
		\Mageplaza\LayeredNavigation\Helper\Data $moduleHelper
	)
	{
		$this->_jsonHelper   = $jsonHelper;
		$this->_moduleHelper = $moduleHelper;
	}

	/**
	 * @param \Magento\Catalog\Controller\Category\View $action
	 * @param $page
	 * @return mixed
	 */
	public function afterExecute(\Magento\Catalog\Controller\Category\View $action, $page)
	{
		if ($this->_moduleHelper->isEnabled() && $action->getRequest()->isAjax()) {
			$navigation = $page->getLayout()->getBlock('catalog.leftnav');
			$products   = $page->getLayout()->getBlock('category.products');
			$result     = ['products' => $products->toHtml(), 'navigation' => $navigation->toHtml()];
			$action->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
		} else {
			return $page;
		}
	}
}
