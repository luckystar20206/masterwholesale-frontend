<?php
namespace ModernRetail\Base\Helper\ApiLogger;
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
    protected $fileName = '/var/log/mr_api.log';
}