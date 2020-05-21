<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Connector\Item\Stop;

/**
 * Class Responser
 * @package Ess\M2ePro\Model\Ebay\Connector\Item\Stop
 */
class Responser extends \Ess\M2ePro\Model\Ebay\Connector\Item\Responser
{
    //########################################

    protected function getSuccessfulMessage()
    {
        return 'Item was successfully Stopped';
    }

    //########################################

    public function eventAfterExecuting()
    {
        parent::eventAfterExecuting();

        $responseData = $this->getPreparedResponseData();

        if (!empty($this->params['params']['remove']) &&
            (!empty($this->params['is_realtime']) || !empty($responseData['request_time']))
        ) {
            $this->listingProduct->setData('status', \Ess\M2ePro\Model\Listing\Product::STATUS_STOPPED);
            $this->listingProduct->delete();
        }
    }

    protected function inspectProduct()
    {
        if (empty($this->params['params']['remove'])) {
            parent::inspectProduct();
            return;
        }

        $responseData = $this->getPreparedResponseData();
        if (!empty($this->params['is_realtime']) || !empty($responseData['request_time'])) {
            return;
        }

        $configurator = $this->getConfigurator();
        if (!empty($responseData['start_processing_date'])) {
            $configurator->setParams(['start_processing_date' => $responseData['start_processing_date']]);
        }

        $this->processAdditionalAction(
            \Ess\M2ePro\Model\Listing\Product::ACTION_STOP,
            $configurator,
            $this->params['params']
        );
    }

    //########################################

    protected function processCompleted(array $data = [], array $params = [])
    {
        if (!empty($data['already_stop'])) {
            $this->getResponseObject()->processSuccess($data, $params);

            // M2ePro\TRANSLATIONS
            // Item was already Stopped on eBay
            $message = $this->modelFactory->getObject('Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'Item was already Stopped on eBay',
                \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage(
                $this->listingProduct,
                $message
            );

            return;
        }

        parent::processCompleted($data, $params);
    }

    //########################################
}
