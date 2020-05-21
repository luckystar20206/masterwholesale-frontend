<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Connector\Orders\Cancel;

/**
 * Class ItemsRequester
 * @package Ess\M2ePro\Model\Amazon\Connector\Orders\Cancel
 */
abstract class ItemsRequester extends \Ess\M2ePro\Model\Amazon\Connector\Command\Pending\Requester
{
    protected $activeRecordFactory;

    // ########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\Account $account = null,
        array $params = []
    ) {
        $this->activeRecordFactory = $activeRecordFactory;
        parent::__construct(
            $helperFactory,
            $modelFactory,
            $account,
            $params
        );
    }

    // ########################################

    public function getCommand()
    {
        return ['orders','cancel','entities'];
    }

    // ########################################

    public function process()
    {
        $this->eventBeforeExecuting();
        $this->getProcessingRunner()->start();
    }

    // ########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Connector_Orders_Cancel_ProcessingRunner';
    }

    protected function getProcessingParams()
    {
        return array_merge(
            parent::getProcessingParams(),
            [
                'request_data' => $this->getRequestData(),
                'order_id'     => $this->params['order']['order_id'],
                'change_id'    => $this->params['order']['change_id'],
                'start_date'   => $this->getHelper('Data')->getCurrentGmtDate(),
            ]
        );
    }

    // ########################################

    protected function getRequestData()
    {
        return $this->params['order']['amazon_order_id'];
    }

    // ########################################
}
