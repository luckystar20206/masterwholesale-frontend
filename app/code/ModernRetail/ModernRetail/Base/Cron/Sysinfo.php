<?php
namespace ModernRetail\Base\Cron;

class Sysinfo
{
    public function __construct(
        \ModernRetail\Base\Helper\Info $infoHelper
    )
    {
       $this->infoHelper = $infoHelper;
    }

    public function execute(){
        $this->infoHelper->sendSystemInformation();
    }

}