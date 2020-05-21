<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Synchronization\ListingsProducts\Update;

use Ess\M2ePro\Model\Processing\Runner;

/**
 * Class Blocked
 * @package Ess\M2ePro\Model\Amazon\Synchronization\ListingsProducts\Update
 */
class Blocked extends \Ess\M2ePro\Model\Amazon\Synchronization\ListingsProducts\AbstractModel
{
    //########################################

    protected function getNick()
    {
        return '/update/blocked/';
    }

    protected function getTitle()
    {
        return 'Update Blocked Listings Products';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 25;
    }

    protected function getPercentsEnd()
    {
        return 50;
    }

    // ---------------------------------------

    protected function intervalIsEnabled()
    {
        return true;
    }

    //########################################

    protected function performActions()
    {
        $accounts = $this->amazonFactory->getObject('Account')->getCollection()->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = $this->getPercentsInterval() / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account \Ess\M2ePro\Model\Account **/

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            $status = 'The "Update Blocked Listings Products" Action for Amazon Account: ';
            $status .= '"%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(
                $this->getHelper('Module\Translation')->__($status, $account->getTitle())
            );

            if (!$this->isLockedAccount($account) && !$this->isLockedAccountInterval($account)) {
                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );

                try {
                    $this->processAccount($account);
                } catch (\Exception $exception) {
                    $message = 'The "Update Blocked Listings Products" Action for Amazon Account "%account%"';
                    $message .= ' was completed with error.';
                    $message = $this->getHelper('Module\Translation')->__($message, $account->getTitle());

                    $this->processTaskAccountException($message, __FILE__, __LINE__);
                    $this->processTaskException($exception);
                }

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

            // M2ePro\TRANSLATIONS
            // The "Update Listings Products" Action for Amazon Account: "%account_title%" is finished. Please wait...
            $status = 'The "Update Blocked Listings Products" Action for Amazon Account: ';
            $status .= '"%account_title%" is finished. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(
                $this->getHelper('Module\Translation')->__($status, $account->getTitle())
            );
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    //########################################

    private function processAccount(\Ess\M2ePro\Model\Account $account)
    {
        $collection = $this->activeRecordFactory->getObject('Listing')->getCollection();
        $collection->addFieldToFilter('component_mode', \Ess\M2ePro\Helper\Component\Amazon::NICK);
        $collection->addFieldToFilter('account_id', (int)$account->getId());

        if ($collection->getSize()) {
            $dispatcherObject = $this->modelFactory->getObject('Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Amazon_Synchronization_ListingsProducts_Update_Blocked_Requester',
                [],
                $account
            );
            $dispatcherObject->process($connectorObj);
        }
    }

    private function isLockedAccount(\Ess\M2ePro\Model\Account $account)
    {
        /** @var $lockItem \Ess\M2ePro\Model\Lock\Item\Manager */
        $lockItem = $this->modelFactory->getObject('Lock_Item_Manager');
        $lockItem->setNick(Blocked\ProcessingRunner::LOCK_ITEM_PREFIX.'_'.$account->getId());
        $lockItem->setMaxInactiveTime(Runner::MAX_LIFETIME);

        return $lockItem->isExist();
    }

    private function isLockedAccountInterval(\Ess\M2ePro\Model\Account $account)
    {
        if ($this->getInitiator() == \Ess\M2ePro\Helper\Data::INITIATOR_USER ||
            $this->getInitiator() == \Ess\M2ePro\Helper\Data::INITIATOR_DEVELOPER) {
            return false;
        }

        $additionalData = $this->getHelper('Data')->jsonDecode($account->getAdditionalData());
        if (!empty($additionalData['last_listing_products_synchronization'])) {
            return (strtotime($additionalData['last_listing_products_synchronization'])
                   + 86400) > $this->getHelper('Data')->getCurrentGmtDate(true);
        }

        return false;
    }

    //########################################
}
