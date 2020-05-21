<?php

namespace ModernRetail\Import\Controller\Remote;

class Summary extends \Magento\Framework\App\Action\Action
{

    const DAYS = 1;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \ModernRetail\Import\Helper\Summary $summaryHelper
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->summaryHelper = $summaryHelper;

        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }
    public function execute()
    {
        $days = self::DAYS;
        if (array_key_exists('days',$_GET)){
            $days = $_GET['days'];
        }
        $result = $this->resultJsonFactory->create();

        $result->setData($this->summaryHelper->getData($days));


        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Modern Retail"');
            header('HTTP/1.0 401 Unauthorized');
            exit;
        } else {
            if ($_SERVER['PHP_AUTH_USER']=='modernretail' && sha1($_SERVER['PHP_AUTH_PW'])=='c2f53a959e41932b204a75d6e8255776e46c34d6' ){
                return $result;
            }
            header('WWW-Authenticate: Basic realm="Modern Retail"');
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }


    }

}