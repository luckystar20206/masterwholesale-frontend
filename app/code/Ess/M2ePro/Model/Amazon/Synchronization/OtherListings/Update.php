<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Synchronization\OtherListings;

use Ess\M2ePro\Model\Processing\Runner;

/**
 * Class Update
 * @package Ess\M2ePro\Model\Amazon\Synchronization\OtherListings
 */
class Update extends AbstractModel
{
    //########################################

    protected function getNick()
    {
        return '/update/';
    }

    protected function getTitle()
    {
        return 'Update';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 30;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    // ---------------------------------------

    protected function intervalIsEnabled()
    {
        return true;
    }

    protected function intervalIsLocked()
    {
        if ($this->getInitiator() == \Ess\M2ePro\Helper\Data::INITIATOR_USER ||
            $this->getInitiator() == \Ess\M2ePro\Helper\Data::INITIATOR_DEVELOPER) {
            return false;
        }

        if (!in_array(
            \Ess\M2ePro\Model\Synchronization\Task\AbstractComponent::LISTINGS_PRODUCTS,
            $this->getAllowedTasksTypes()
        )) {
            return parent::intervalIsLocked();
        }

        $operationHistory = $this->getActualOperationHistory()->getParentObject('synchronization_amazon');
        if ($operationHistory === null) {
            return parent::intervalIsLocked();
        }

        $synchronizationStartTime = $operationHistory->getData('start_date');
        $updateListingsProductsLastTime = $this->getConfigValue(
            '/amazon/listings_products/update/',
            'last_time'
        );

        return strtotime($synchronizationStartTime) > strtotime($updateListingsProductsLastTime);
    }

    //########################################

    protected function performActions()
    {
        $accountsCollection = $this->amazonFactory->getObject('Account')->getCollection();
        $accountsCollection->addFieldToFilter(
            'other_listings_synchronization',
            \Ess\M2ePro\Model\Amazon\Account::OTHER_LISTINGS_SYNCHRONIZATION_YES
        );

        $accounts = $accountsCollection->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = $this->getPercentsInterval() / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account \Ess\M2ePro\Model\Account **/

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro\TRANSLATIONS
            // The "3rd Party Listings" Action for Amazon Account: "%account_title%" is started. Please wait...
            $status = 'The "3rd Party Listings" Action for Amazon Account: "%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(
                $this->getHelper('Module\Translation')->__($status, $account->getTitle())
            );

            if (!$this->isLockedAccount($account)) {
                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );

                try {
                    $params = [];
                    if (!$this->isFullItemsDataAlreadyReceived($account)) {
                        $params['full_items_data'] = true;

                        $additionalData = (array)$this->getHelper('Data')->jsonDecode($account->getAdditionalData());
                        $additionalData['is_amazon_other_listings_full_items_data_already_received'] = true;
                        $account->setSettings('additional_data', $additionalData)->save();
                    }

                    $dispatcherObject = $this->modelFactory->getObject('Amazon_Connector_Dispatcher');
                    $connectorObj = $dispatcherObject->getCustomConnector(
                        'Amazon_Synchronization_OtherListings_Update_Requester',
                        $params,
                        $account
                    );

                    $dispatcherObject->process($connectorObj);
                } catch (\Exception $exception) {
                    $message = $this->getHelper('Module\Translation')->__(
                        'The "3rd Party Listings" Action for Amazon Account "%account%" was completed with error.',
                        $account->getTitle()
                    );

                    $this->processTaskAccountException($message, __FILE__, __LINE__);
                    $this->processTaskException($exception);
                }

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

            // M2ePro\TRANSLATIONS
            // The "3rd Party Listings" Action for Amazon Account: "%account_title%" is finished. Please wait...
            $status = 'The "3rd Party Listings" Action for Amazon Account: "%account_title%" is finished. ';
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

    private function isLockedAccount(\Ess\M2ePro\Model\Account $account)
    {
        /** @var $lockItem \Ess\M2ePro\Model\Lock\Item\Manager */
        $lockItem = $this->modelFactory->getObject('Lock_Item_Manager');
        $lockItem->setNick(Update\ProcessingRunner::LOCK_ITEM_PREFIX.'_'.$account->getId());
        $lockItem->setMaxInactiveTime(Runner::MAX_LIFETIME);

        return $lockItem->isExist();
    }

    private function isFullItemsDataAlreadyReceived(\Ess\M2ePro\Model\Account $account)
    {
        $additionalData = (array)$this->getHelper('Data')->jsonDecode($account->getAdditionalData());
        return !empty($additionalData['is_amazon_other_listings_full_items_data_already_received']);
    }

    //########################################
}
