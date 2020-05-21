<?php
namespace ModernRetail\Import\Block\System\Config\Fieldset\Mapping;



use Magento\Backend\Block\Template;

class Render extends Template{

    public $scopeConfig;
   
    public function __construct(    \Magento\Backend\Block\Template\Context $context) 
    {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, array()); 
    }

    public function getTemplate(){

        return "system/config/fieldset/mapping.phtml";
    }



}
