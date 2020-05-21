<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Cron\Task\Amazon;

/**
 * Class RepricingInspectProducts
 * @package Ess\M2ePro\Model\Cron\Task\Amazon
 */
class RepricingInspectProducts extends \Ess\M2ePro\Model\Cron\Task\AbstractModel
{
    const NICK = 'amazon/repricing_inspect_products';
    const MAX_MEMORY_LIMIT = 512;

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return self::MAX_MEMORY_LIMIT;
    }

    //####################################

    public function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();

        foreach ($permittedAccounts as $permittedAccount) {
            $operationDate = $this->getHelper('Data')->getCurrentGmtDate();
            $skus = $this->getNewNoneSyncSkus($permittedAccount);

            if (empty($skus)) {
                continue;
            }

            /** @var $repricingSynchronization \Ess\M2ePro\Model\Amazon\Repricing\Synchronization\General */
            $repricingSynchronization = $this->modelFactory->getObject('Amazon_Repricing_Synchronization_General');
            $repricingSynchronization->setAccount($permittedAccount);
            $repricingSynchronization->run($skus);
            $this->getLockItem()->activate();

            $this->setLastUpdateDate($permittedAccount, $operationDate);
        }
    }

    //####################################

    /**
     * @return \Ess\M2ePro\Model\Account[]
     */
    private function getPermittedAccounts()
    {
        $accountCollection = $this->activeRecordFactory->getObject('Account')->getCollection();

        $accountCollection->getSelect()->joinInner(
            [
                'aar' => $this->getHelper('Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_account_repricing')
            ],
            'aar.account_id=main_table.id',
            []
        );

        return $accountCollection->getItems();
    }

    /**
     * @param $account \Ess\M2ePro\Model\Account
     * @return array
     */
    private function getNewNoneSyncSkus(\Ess\M2ePro\Model\Account $account)
    {
        $accountId = $account->getId();

        $listingProductCollection = $this->parentFactory->getObject(
            \Ess\M2ePro\Helper\Component\Amazon::NICK,
            'Listing\Product'
        )->getCollection();

        $listingProductCollection->getSelect()->join(
            ['l' => $this->getHelper('Module_Database_Structure')->getTableNameWithPrefix('m2epro_listing')],
            'l.id=main_table.listing_id',
            []
        );
        $listingProductCollection->addFieldToFilter('l.account_id', $accountId);
        $listingProductCollection->addFieldToFilter(
            'main_table.status',
            [
                'in' => [
                    \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED,
                    \Ess\M2ePro\Model\Listing\Product::STATUS_STOPPED,
                    \Ess\M2ePro\Model\Listing\Product::STATUS_UNKNOWN
                ]
            ]
        );
        $listingProductCollection->addFieldToFilter(
            'main_table.update_date',
            ['gt' => $this->getLastUpdateDate($account)]
        );

        $listingProductCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $listingProductCollection->getSelect()->columns('second_table.sku');

        return $listingProductCollection->getColumnValues('sku');
    }

    /**
     * @param $account \Ess\M2ePro\Model\Account
     * @return string
     */
    private function getLastUpdateDate(\Ess\M2ePro\Model\Account $account)
    {
        $accountId = $account->getId();

        $lastCheckedUpdateTime = $this->activeRecordFactory->getObjectLoaded('Amazon_Account_Repricing', $accountId)
            ->getLastCheckedListingProductDate();

        if ($lastCheckedUpdateTime === null) {
            $lastCheckedUpdateTime = new \DateTime(
                $this->getHelper('Data')->getCurrentGmtDate(),
                new \DateTimeZone('UTC')
            );
            $lastCheckedUpdateTime->modify('-1 hour');
            $lastCheckedUpdateTime = $lastCheckedUpdateTime->format('Y-m-d H:i:s');
        }

        return $lastCheckedUpdateTime;
    }

    /**
     * @param $account \Ess\M2ePro\Model\Account
     * @param $syncDate \Datetime|String
     */
    private function setLastUpdateDate(\Ess\M2ePro\Model\Account $account, $syncDate)
    {
        $accountId = $account->getId();

        /** @var $accountRepricingModel \Ess\M2ePro\Model\Amazon\Account\Repricing */
        $accountRepricingModel = $this->activeRecordFactory->getObjectLoaded('Amazon_Account_Repricing', $accountId);
        $accountRepricingModel->setData(
            'last_checked_listing_product_update_date',
            $syncDate
        );

        $accountRepricingModel->save();
    }

    //####################################
}
