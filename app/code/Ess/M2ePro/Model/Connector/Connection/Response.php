<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Connector\Connection;

/**
 * Class Response
 * @package Ess\M2ePro\Model\Connector\Connection
 */
class Response extends \Ess\M2ePro\Model\AbstractModel
{
    private $data = [];

    /** @var \Ess\M2ePro\Model\Connector\Connection\Response\Message\Set $messages */
    private $messages = null;

    private $resultType = \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_SUCCESS;

    private $requestTime = null;

    // ########################################

    public function initFromRawResponse($response)
    {
        $response = $this->getHelper('Data')->jsonDecode($response);

        if (!is_array($response) ||
            !isset($response['data']) || !is_array($response['data']) ||
            !isset($response['response']['result']['messages']) ||
            !is_array($response['response']['result']['messages']) ||
            !isset($response['response']['result']['type'])) {
            throw new \Ess\M2ePro\Model\Exception\Connection\InvalidResponse('Invalid Response Format.');
        }

        $this->data = $response['data'];

        $this->initMessages($response['response']['result']['messages']);
        $this->initResultType($response['response']['result']['type']);
    }

    public function initFromPreparedResponse(array $data = [], array $messagesData = [], $resultType = null)
    {
        $this->data = $data;

        $this->initMessages($messagesData);
        $this->initResultType($resultType);
    }

    // ########################################

    public function getResult()
    {
        return $this->resultType;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getResponseData()
    {
        return $this->data;
    }

    // ########################################

    public function isResultError()
    {
        return $this->resultType == \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_ERROR;
    }

    public function isResultWarning()
    {
        return $this->resultType == \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_WARNING;
    }

    public function isResultSuccess()
    {
        return $this->resultType == \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_SUCCESS;
    }

    public function isResultNotice()
    {
        return $this->resultType == \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_NOTICE;
    }

    // ########################################

    public function setRequestTime($requestTime)
    {
        $this->requestTime = $requestTime;
        return $this;
    }

    public function getRequestTime()
    {
        return $this->requestTime;
    }

    // ########################################

    public function isServerInMaintenanceMode()
    {
        if (!$this->getMessages()->hasSystemErrorEntity()) {
            return false;
        }

        foreach ($this->getMessages()->getErrorEntities() as $message) {
            if (!$message->isSenderSystem()) {
                continue;
            }

            if ($message->getCode() == 3) {
                return true;
            }
        }

        return false;
    }

    // ########################################

    private function initMessages(array $messagesData)
    {
        $this->messages = $this->modelFactory->getObject('Connector_Connection_Response_Message_Set');
        $this->messages->init($messagesData);
    }

    private function initResultType($resultType = null)
    {
        if ($resultType !== null) {
            $this->resultType = $resultType;
            return;
        }

        if ($this->getMessages()->hasErrorEntities()) {
            $this->resultType = \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_ERROR;
            return;
        }

        if ($this->getMessages()->hasWarningEntities()) {
            $this->resultType = \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_WARNING;
            return;
        }

        $this->resultType = \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_SUCCESS;
    }

    // ########################################
}
