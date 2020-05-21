<?php
namespace ModernRetail\Import\Controller;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;


abstract class RemoteAbstract extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \ModernRetail\Import\Model\Xml $import,
        \ModernRetail\Import\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {

        $this->import = $import;
        $this->helper = $helper;
        $this->resource = $resource;
        $this->_storeManager = $storeManager;
        $this->eventManager = $context->getEventManager();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $encryptor = $objectManager->get('\Magento\Framework\Encryption\EncryptorInterface');
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        parent::__construct($context);
        $XLogin = $this->getRequest()->getHeader("X-Login");
        $XPassword = $this->getRequest()->getHeader("X-Password");

        /**
         * Check if enabled protected mode
         */
        $isProtected = $scopeConfig->getValue('modernretail_import/settings/protect_mode');

        if ($isProtected) {

            $login = $scopeConfig->getValue(\ModernRetail\Import\Helper\Monitor\Api::XML_CONFIG_API_LOGIN);
            $password = $scopeConfig->getValue(\ModernRetail\Import\Helper\Monitor\Api::XML_CONFIG_API_PASSWORD);
            $password = $encryptor->decrypt($password);

            $accessDenied = false;

            if (!$login || !$password) {
                $accessDenied = true;
            }

            if ($login != $XLogin || $password != $XPassword) {
                $accessDenied = true;
            }


            if (@$_COOKIE['developer'] == 1) {
                $accessDenied = false;
            }

            if ($accessDenied === true) {
                die("ACCESS DENIED");
            }
        }

    }



    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }


}