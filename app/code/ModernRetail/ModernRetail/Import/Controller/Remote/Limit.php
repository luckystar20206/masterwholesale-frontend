<?php
namespace ModernRetail\Import\Controller\Remote;

class Limit extends \ModernRetail\Import\Controller\RemoteAbstract
{

    public function execute()
    {
        die($this->helper->scopeConfig->getValue("modernretail_import/settings/limit"));
    }
}