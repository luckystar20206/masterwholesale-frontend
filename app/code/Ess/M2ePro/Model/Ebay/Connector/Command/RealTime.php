<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Connector\Command;

/**
 * Class RealTime
 * @package Ess\M2ePro\Model\Ebay\Connector\Command
 */
abstract class RealTime extends \Ess\M2ePro\Model\Connector\Command\RealTime
{
    /**
     * @var \Ess\M2ePro\Model\Marketplace|null
     */
    protected $marketplace = null;

    /**
     * @var \Ess\M2ePro\Model\Account|null
     */
    protected $account = null;

    // ########################################

    public function __construct(
        \Ess\M2ePro\Model\Marketplace $marketplace,
        \Ess\M2ePro\Model\Account $account,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        array $params
    ) {
        $this->marketplace = ($marketplace->getId() !== null) ? $marketplace : null;
        $this->account = ($account->getId() !== null) ? $account : null;

        parent::__construct($helperFactory, $modelFactory, $params);
    }

    // ########################################

    protected function buildRequestInstance()
    {
        $request = parent::buildRequestInstance();

        $requestData = $request->getData();

        if ($this->marketplace !== null) {
            $requestData['marketplace'] = $this->marketplace->getNativeId();
        }
        if ($this->account !== null) {
            $requestData['account'] = $this->account->getChildObject()->getServerHash();
        }

        $request->setData($requestData);

        return $request;
    }

    // ########################################
}
