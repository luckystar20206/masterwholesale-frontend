<?php

namespace ModernRetail\Import\Controller\Remote;

class Log extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    )
    {

        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }
    public function execute()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Modern Retail"');
            header('HTTP/1.0 401 Unauthorized');
            exit;
        } else {
            if ($_SERVER['PHP_AUTH_USER']=='modernretail' && sha1($_SERVER['PHP_AUTH_PW'])=='c2f53a959e41932b204a75d6e8255776e46c34d6' ){
                $result = $this->_pageFactory->create();
                return $result;
            }
            header('WWW-Authenticate: Basic realm="Modern Retail"');
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }

    }

}