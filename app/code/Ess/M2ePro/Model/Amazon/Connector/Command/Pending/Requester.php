<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Connector\Command\Pending;

/**
 * Class Requester
 * @package Ess\M2ePro\Model\Amazon\Connector\Command\Pending
 */
abstract class Requester extends \Ess\M2ePro\Model\Connector\Command\Pending\Requester
{
    /**
     * @var \Ess\M2ePro\Model\Account|null
     */
    protected $account = null;

    //########################################

    /**
     * Requester constructor.
     * @param \Ess\M2ePro\Helper\Factory $helperFactory
     * @param \Ess\M2ePro\Model\Factory $modelFactory
     * @param \Ess\M2ePro\Model\Account $account
     * @param array $params
     */
    public function __construct(
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\Account $account = null,
        array $params = []
    ) {
        $this->account = $account;
        parent::__construct($helperFactory, $modelFactory, $params);
    }

    //########################################

    protected function buildRequestInstance()
    {
        $request = parent::buildRequestInstance();

        $requestData = $request->getData();
        if ($this->account !== null) {
            $requestData['account'] = $this->account->getChildObject()->getServerHash();
        }
        $request->setData($requestData);

        return $request;
    }

    //########################################

    protected function getProcessingParams()
    {
        $params = parent::getProcessingParams();

        if ($this->account !== null) {
            $params['account_id'] = $this->account->getId();
        }

        return $params;
    }

    protected function getResponserParams()
    {
        $params = parent::getResponserParams();

        if ($this->account !== null) {
            $params['account_id'] = $this->account->getId();
        }

        return $params;
    }

    //########################################
}
