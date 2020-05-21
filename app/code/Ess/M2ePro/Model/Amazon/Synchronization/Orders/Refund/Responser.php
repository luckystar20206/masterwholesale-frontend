<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Synchronization\Orders\Refund;

/**
 * Class Responser
 * @package Ess\M2ePro\Model\Amazon\Synchronization\Orders\Refund
 */
class Responser extends \Ess\M2ePro\Model\Amazon\Connector\Orders\Refund\ItemsResponser
{
    /** @var \Ess\M2ePro\Model\Order $order */
    private $order = null;

    protected $activeRecordFactory;

    // ########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Model\Connector\Connection\Response $response,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        array $params = []
    ) {
        $this->activeRecordFactory = $activeRecordFactory;
        parent::__construct($amazonFactory, $response, $helperFactory, $modelFactory, $params);

        $this->order = $this->activeRecordFactory->getObjectLoaded('Order', $this->params['order']['order_id']);
    }

    // ########################################

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $this->order->getLog()->setInitiator(\Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION);
        $this->order->addErrorLog('Amazon Order was not refunded. Reason: %msg%', ['msg' => $messageText]);
    }

    // ########################################

    protected function processResponseData()
    {
        $this->activeRecordFactory->getObject('Order\Change')->getResource()
             ->deleteByIds([$this->params['order']['change_id']]);

        $responseData = $this->getPreparedResponseData();

        // Check separate messages
        //----------------------
        $isFailed = false;

        /** @var \Ess\M2ePro\Model\Connector\Connection\Response\Message\Set $messagesSet */
        $messagesSet = $this->modelFactory->getObject('Connector_Connection_Response_Message_Set');
        $messagesSet->init($responseData['messages']);

        foreach ($messagesSet->getEntities() as $message) {
            $this->order->getLog()->setInitiator(\Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION);

            if ($message->isError()) {
                $isFailed = true;
                $this->order->addErrorLog(
                    'Amazon Order was not refunded. Reason: %msg%',
                    ['msg' => $message->getText()]
                );
            } else {
                $this->order->addWarningLog($message->getText());
            }
        }
        //----------------------

        if ($isFailed) {
            return;
        }

        //----------------------
        $this->order->getLog()->setInitiator(\Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION);
        $this->order->addSuccessLog('Amazon Order was refunded.');
        //----------------------
    }

    // ########################################
}
