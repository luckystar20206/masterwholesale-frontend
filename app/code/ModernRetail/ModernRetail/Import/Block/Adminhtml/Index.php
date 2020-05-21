<?php

namespace ModernRetail\Import\Block\Adminhtml;

/**
 * Banner grid container.
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Index extends \Magento\Backend\Block\Template
{
    public $helper;
    public $mageHelper;
    public $request;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \ModernRetail\Import\Helper\Data $helper,
        \Magento\Backend\Helper\Data $mageHelper,
        \ModernRetail\Import\Helper\Version $versionHelper,

        array $data = []
    )
    {
        //$this->setTemplate("import/index.phtml");
        $this->helper = $helper;
        $this->request = $request;
        $this->mageHelper = $mageHelper;
        $this->versionHelper = $versionHelper;

        parent::__construct($context,$data);
    }


    public function getFiles(){

        return $this->helper->getFiles($this->getBucket());
    }

    public function getBuckets(){
        return $this->helper->getBuckets();
    }


    public function getBucket(){
        return $this->request->getParam("bucket");
    }
}
