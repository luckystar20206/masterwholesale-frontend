<?php
namespace ModernRetail\Import\Helper\Monitor\Logger;
use Monolog\Logger; 

class  Handler extends \Magento\Framework\Logger\Handler\Base{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /** 
     * File name
     * @var string
     */
    protected $fileName = '/var/log/modernretail_monitor_api.log';
}