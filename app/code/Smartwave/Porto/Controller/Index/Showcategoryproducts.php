<?php
namespace Smartwave\Porto\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Showcategoryproducts extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_redirect('/');
            return;
        }

        $params = $this->getRequest()->getParams();
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock('Smartwave\Filterproducts\Block\Home\LatestList')
            ->setTemplate('Smartwave_Porto::ajaxproducts/grid.phtml')
            ->setData('category_id',$params['category_id'])
            ->setData('product_count',$params['product_count'])
            ->setData('aspect_ratio',$params['aspect_ratio'])
            ->setData('image_width',$params['image_width'])
            ->setData('image_height',$params['image_height'])
            ->setData('column_count',$params['columns'])
            ->toHtml();
        $jsonData = json_encode(array('result' => $block));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}
