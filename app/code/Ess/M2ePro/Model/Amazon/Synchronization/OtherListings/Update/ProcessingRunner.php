<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Synchronization\OtherListings\Update;

/**
 * Class ProcessingRunner
 * @package Ess\M2ePro\Model\Amazon\Synchronization\OtherListings\Update
 */
class ProcessingRunner extends \Ess\M2ePro\Model\Connector\Command\Pending\Processing\Runner\Partial
{
    const LOCK_ITEM_PREFIX = 'synchronization_amazon_other_listings_update';

    // ##################################

    protected function setLocks()
    {
        parent::setLocks();

        $params = $this->getParams();

        /** @var $lockItem \Ess\M2ePro\Model\Lock\Item\Manager */
        $lockItem = $this->modelFactory->getObject('Lock_Item_Manager');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$params['account_id']);
        $lockItem->setMaxInactiveTime(self::MAX_LIFETIME);
        $lockItem->create();

        /** @var \Ess\M2ePro\Model\Account $account */
        $account = $this->parentFactory->getCachedObjectLoaded(
            \Ess\M2ePro\Helper\Component\Amazon::NICK,
            'Account',
            $params['account_id']
        );

        $account->addProcessingLock(null, $this->getProcessingObject()->getId());
        $account->addProcessingLock('synchronization', $this->getProcessingObject()->getId());
        $account->addProcessingLock('synchronization_amazon', $this->getProcessingObject()->getId());
        $account->addProcessingLock(self::LOCK_ITEM_PREFIX, $this->getProcessingObject()->getId());
    }

    protected function unsetLocks()
    {
        parent::unsetLocks();

        $params = $this->getParams();

        /** @var $lockItem \Ess\M2ePro\Model\Lock\Item\Manager */
        $lockItem = $this->modelFactory->getObject('Lock_Item_Manager');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$params['account_id']);
        $lockItem->remove();

        /** @var \Ess\M2ePro\Model\Account $account */
        $account = $this->parentFactory->getCachedObjectLoaded(
            \Ess\M2ePro\Helper\Component\Amazon::NICK,
            'Account',
            $params['account_id']
        );

        $account->deleteProcessingLocks(null, $this->getProcessingObject()->getId());
        $account->deleteProcessingLocks('synchronization', $this->getProcessingObject()->getId());
        $account->deleteProcessingLocks('synchronization_amazon', $this->getProcessingObject()->getId());
        $account->deleteProcessingLocks(self::LOCK_ITEM_PREFIX, $this->getProcessingObject()->getId());
    }

    // ##################################
}
