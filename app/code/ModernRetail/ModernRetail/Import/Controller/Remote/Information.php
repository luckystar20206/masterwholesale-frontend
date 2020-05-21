<?php

namespace ModernRetail\Import\Controller\Remote;

class Information extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \ModernRetail\Base\Helper\Info $infoHelper
    )
    {
        $this->infoHelper = $infoHelper;
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->infoHelper->sendSystemInformation();
    }

}