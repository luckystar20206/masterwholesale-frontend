<?php
namespace ModernRetail\Import\Controller\Remote;

class Version extends \Magento\Framework\App\Action\Action
{
	  public function __construct(  \Magento\Framework\App\Action\Context $context, \ModernRetail\Import\Helper\Version $versionHelper)
    {
      	$this->versionHelper = $versionHelper;
		parent::__construct($context);
    }
	
    public function execute()
    {
    	die($this->versionHelper->getCurrentVersion());  
    }
	
}