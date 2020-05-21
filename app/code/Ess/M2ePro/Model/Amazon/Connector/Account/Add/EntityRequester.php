<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Connector\Account\Add;

/**
 * Class EntityRequester
 * @package Ess\M2ePro\Model\Amazon\Connector\Account\Add
 */
class EntityRequester extends \Ess\M2ePro\Model\Amazon\Connector\Command\Pending\Requester
{
    protected $amazonFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\Account $account = null,
        array $params = []
    ) {
        $this->amazonFactory = $amazonFactory;
        parent::__construct($helperFactory, $modelFactory, $account, $params);
    }

    //########################################

    protected function getRequestData()
    {
        /** @var $marketplaceObject \Ess\M2ePro\Model\Marketplace */

        $marketplaceObject = $this->amazonFactory->getCachedObjectLoaded(
            'Marketplace',
            $this->params['marketplace_id']
        );

        return [
            'title'          => $this->account->getTitle(),
            'merchant_id'    => $this->params['merchant_id'],
            'token'          => $this->params['token'],
            'marketplace_id' => $marketplaceObject->getNativeId(),
        ];
    }

    protected function getCommand()
    {
        return ['account','add','entity'];
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Connector_Account_Add_ProcessingRunner';
    }

    //########################################
}
