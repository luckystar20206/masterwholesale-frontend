<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Synchronization\Orders\Update;

/**
 * Class Responser
 * @package Ess\M2ePro\Model\Amazon\Synchronization\Orders\Update
 */
class Responser extends \Ess\M2ePro\Model\Amazon\Connector\Orders\Update\ItemsResponser
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

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $this->order->getLog()->setInitiator(\Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION);
        $this->order->addErrorLog('Amazon Order status was not updated. Reason: %msg%', ['msg' => $messageText]);
    }

    // ########################################

    protected function processResponseData()
    {
        $this->activeRecordFactory->getObject('Order\Change')->getResource()
             ->deleteByIds([$this->params['order']['change_id']]);

        $responseData = $this->getResponse()->getResponseData();

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
                    'Amazon Order status was not updated. Reason: %msg%',
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
        $this->order->addSuccessLog('Amazon Order status was updated to Shipped.');

        if (empty($this->params['order']['tracking_number']) || empty($this->params['order']['carrier_name'])) {
            return;
        }

        $this->order->addSuccessLog(
            'Tracking number "%num%" for "%code%" has been sent to Amazon.',
            [
                '!num' => $this->params['order']['tracking_number'],
                'code' => $this->params['order']['carrier_name']
            ]
        );
        //----------------------
    }

    // ########################################
}
