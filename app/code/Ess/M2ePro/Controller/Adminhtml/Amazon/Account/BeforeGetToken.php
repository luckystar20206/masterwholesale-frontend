<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Account;

use Ess\M2ePro\Controller\Adminhtml\Amazon\Account;

/**
 * Class BeforeGetToken
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon\Account
 */
class BeforeGetToken extends Account
{
    public function execute()
    {
        // Get and save form data
        // ---------------------------------------
        $accountId = $this->getRequest()->getParam('id', 0);
        $accountTitle = $this->getRequest()->getParam('title', '');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', 0);
        // ---------------------------------------

        $marketplace = $this->activeRecordFactory->getObjectLoaded('Marketplace', $marketplaceId);

        try {
            $backUrl = $this->getUrl('*/*/afterGetToken', ['_current' => true]);

            $dispatcherObject = $this->modelFactory->getObject('Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account',
                'get',
                'authUrl',
                ['back_url' => $backUrl, 'marketplace' => $marketplace->getData('native_id')]
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (\Exception $exception) {
            $this->getHelper('Module\Exception')->process($exception);
            // M2ePro_TRANSLATIONS
            // The Amazon token obtaining is currently unavailable.<br/>Reason: %error_message%
            $error = 'The Amazon token obtaining is currently unavailable.<br/>Reason: %error_message%';
            $error = $this->__($error, $exception->getMessage());

            $this->setJsonContent([
                'message' => $error
            ]);
            return $this->getResult();
        }

        $this->getHelper('Data\Session')->setValue('account_id', $accountId);
        $this->getHelper('Data\Session')->setValue('account_title', $accountTitle);
        $this->getHelper('Data\Session')->setValue('marketplace_id', $marketplaceId);

        $this->getResponse()->setRedirect($response['url']);
        return $this->getResponse();

        // ---------------------------------------
    }
}
