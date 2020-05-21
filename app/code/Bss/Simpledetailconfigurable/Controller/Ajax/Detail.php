<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Controller\Ajax;
 
use Magento\Framework\App\Action\Context;
 
class Detail extends \Magento\Framework\App\Action\Action
{
    private $productData;

    private $resultJsonFactory;

    public function __construct(
        Context $context,
        \Bss\Simpledetailconfigurable\Helper\ProductData $productData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->productData = $productData;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory  = $resultPageFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {   $this->resultPageFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $childId = $this->getRequest()->getParam('product_id');
            $result = $this->productData->getChildDetail($childId);
            return $resultJson->setData($result);
        } else {
            return $resultJson->setData(null);
        }
    }
}
