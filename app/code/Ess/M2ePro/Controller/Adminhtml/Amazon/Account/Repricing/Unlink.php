<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Account\Repricing;

use Ess\M2ePro\Controller\Adminhtml\Amazon\Account;

/**
 * Class Unlink
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon\Account\Repricing
 */
class Unlink extends Account
{
    public function execute()
    {
        $accountId = $this->getRequest()->getParam('id');

        $status   = $this->getRequest()->getParam('status');
        $messages = $this->getRequest()->getParam('messages', []);

        /** @var $account \Ess\M2ePro\Model\Account */
        $account = $this->amazonFactory->getObjectLoaded('Account', $accountId, null, false);

        if ($accountId && $account === null) {
            $this->getMessageManager()->addError($this->__('Account does not exist.'));
            return $this->_redirect('*/amazon_account/index');
        }

        foreach ($messages as $message) {
            if ($message['type'] == 'notice') {
                $this->getMessageManager()->addNotice($message['text']);
            }

            if ($message['type'] == 'warning') {
                $this->getMessageManager()->addWarning($message['text']);
            }

            if ($message['type'] == 'error') {
                $this->getMessageManager()->addError($message['text']);
            }
        }

        if ($status == '1') {
            /** @var $repricingSynchronization \Ess\M2ePro\Model\Amazon\Repricing\Synchronization\General */
            $repricingSynchronization = $this->modelFactory->getObject('Amazon_Repricing_Synchronization_General');
            $repricingSynchronization->setAccount($account);
            $repricingSynchronization->reset();

            $account->getChildObject()->getRepricing()->delete();
        }

        return $this->_redirect(
            $this->getUrl('*/amazon_account/edit', ['id' => $accountId]).'#repricing'
        );
    }
}
