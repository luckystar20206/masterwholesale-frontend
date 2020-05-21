<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Synchronization\OtherListings;

/**
 * Class Title
 * @package Ess\M2ePro\Model\Amazon\Synchronization\OtherListings
 */
class Title extends AbstractModel
{
    //########################################

    protected function getNick()
    {
        return '/title/';
    }

    protected function getTitle()
    {
        return 'Title';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 90;
    }

    protected function getPercentsEnd()
    {
        return 100;
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

        if (empty($accounts)) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = ($this->getPercentsInterval() / 2) / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account \Ess\M2ePro\Model\Account **/

            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Get and process Titles for Account '.$account->getTitle()
            );

            try {
                $this->updateTitlesByAsins($account);
            } catch (\Exception $exception) {
                $message = $this->getHelper('Module\Translation')->__(
                    'The "Update Titles" Action for Amazon Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());

            $offset = $this->getPercentsInterval() / 2 + $iteration * $percentsForOneStep;
            $this->getActualLockItem()->setPercents($offset);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    //########################################

    private function updateTitlesByAsins(\Ess\M2ePro\Model\Account $account)
    {
        for ($i = 0; $i <= 5; $i++) {
            $listingOtherCollection = $this->amazonFactory->getObject('Listing\Other')->getCollection();
            $listingOtherCollection->addFieldToFilter('main_table.account_id', (int)$account->getId());
            $listingOtherCollection->getSelect()->where('`second_table`.`title` IS NULL');
            $listingOtherCollection->getSelect()->order('main_table.create_date ASC');
            $listingOtherCollection->getSelect()->limit(5);

            if (!$listingOtherCollection->getSize()) {
                return;
            }

            $neededItems = [];
            foreach ($listingOtherCollection->getItems() as $tempItem) {
                /**@var $tempItem \Ess\M2ePro\Model\Listing\Other  */
                $neededItems[] = $tempItem->getChildObject()->getData('general_id');
            }

            /** @var \Ess\M2ePro\Model\Amazon\Connector\Dispatcher $dispatcherObject */
            $dispatcherObject = $this->modelFactory->getObject('Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'product',
                'search',
                'byIdentifiers',
                [
                    'items'         => $neededItems,
                    'id_type'       => 'ASIN',
                    'only_realtime' => 1
                ],
                null,
                $account->getId()
            );

            $dispatcherObject->process($connectorObj);
            $responseData = $connectorObj->getResponseData();

            if (!empty($responseData['unavailable']) && $responseData['unavailable'] == true) {
                return;
            }

            $this->updateReceivedTitles($responseData, $account);
            $this->updateNotReceivedTitles($neededItems, $responseData);
        }
    }

    // ---------------------------------------

    private function updateReceivedTitles(array $responseData, \Ess\M2ePro\Model\Account $account)
    {
        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return;
        }

        $connWrite = $this->resourceConnection->getConnection();

        $aloTable = $this->activeRecordFactory->getObject('Amazon_Listing_Other')->getResource()->getMainTable();
        $lolTable = $this->activeRecordFactory->getObject('Listing_Other_Log')->getResource()->getMainTable();

        /** @var $mappingModel \Ess\M2ePro\Model\Amazon\Listing\Other\Mapping */
        $mappingModel = $this->modelFactory->getObject('Amazon_Listing_Other_Mapping');

        /** @var $movingModel \Ess\M2ePro\Model\Amazon\Listing\Other\Moving */
        $movingModel = $this->modelFactory->getObject('Amazon_Listing_Other_Moving');

        $receivedItems = [];
        foreach ($responseData['items'] as $generalId => $item) {
            if ($item == false) {
                continue;
            }

            $item = array_shift($item);
            $title = $item['title'];

            if (isset($receivedItems[$generalId]) || empty($title)) {
                continue;
            }

            $receivedItems[$generalId] = $title;

            $listingsOthersWithEmptyTitles = [];
            if ($account->getChildObject()->isOtherListingsMappingEnabled()) {
                $listingOtherCollection = $this->amazonFactory->getObject('Listing\Other')->getCollection()
                    ->addFieldToFilter('main_table.account_id', (int)$account->getId())
                    ->addFieldToFilter('second_table.general_id', (int)$generalId)
                    ->addFieldToFilter('second_table.title', ['null' => true]);

                $listingsOthersWithEmptyTitles = $listingOtherCollection->getItems();
            }

            $connWrite->update(
                $aloTable,
                ['title' => (string)$title],
                ['general_id = ?' => (string)$generalId]
            );

            $connWrite->update(
                $lolTable,
                ['title' => (string)$title],
                [
                    'identifier = ?' => (string)$generalId,
                    'component_mode = ?' => \Ess\M2ePro\Helper\Component\Amazon::NICK
                ]
            );

            if (!empty($listingsOthersWithEmptyTitles)) {
                foreach ($listingsOthersWithEmptyTitles as $listingOtherModel) {
                    $listingOtherModel->setData('title', (string)$title);
                    $listingOtherModel->getChildObject()->setData('title', (string)$title);

                    $mappingModel->initialize($account);
                    $mappingResult = $mappingModel->autoMapOtherListingProduct($listingOtherModel);

                    if ($mappingResult) {
                        if (!$account->getChildObject()->isOtherListingsMoveToListingsEnabled()) {
                            continue;
                        }

                        $movingModel->initialize($account);
                        $movingModel->autoMoveOtherListingProduct($listingOtherModel);
                    }
                }
            }
        }
    }

    private function updateNotReceivedTitles($neededItems, $responseData)
    {
        $connWrite = $this->resourceConnection->getConnection();

        $aloTable = $this->activeRecordFactory->getObject('Amazon_Listing_Other')->getResource()->getMainTable();

        foreach ($neededItems as $generalId) {
            if (isset($responseData['items'][$generalId]) &&
                !empty($responseData['items'][$generalId][0]['title'])) {
                continue;
            }

            $connWrite->update(
                $aloTable,
                ['title' => \Ess\M2ePro\Model\Amazon\Listing\Other::EMPTY_TITLE_PLACEHOLDER],
                ['general_id = ?' => (string)$generalId]
            );
        }
    }

    //########################################
}
