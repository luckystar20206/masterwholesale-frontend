<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Connector;

/**
 * Class AbstractModel
 * @package Ess\M2ePro\Model\Connector
 */
abstract class AbstractModel extends \Ess\M2ePro\Model\AbstractModel
{
    const API_VERSION = 1;

    protected $requestTime = null;

    protected $serverBaseUrl = null;
    protected $serverHostName = null;

    protected $tryToResendOnError = true;
    protected $tryToSwitchEndpointOnError = true;

    // ########################################

    public function process()
    {
        try {
            $this->requestTime = $this->getHelper('Data')->getCurrentGmtDate();

            $result = $this->sendRequest();
        } catch (\Exception $exception) {
            $this->getHelper('Client')->updateMySqlConnection();
            throw $exception;
        }

        $this->getHelper('Client')->updateMySqlConnection();

        $this->processRequestResult($result);
    }

    // ----------------------------------------

    abstract protected function sendRequest();

    abstract protected function processRequestResult(array $result);

    // ########################################

    public function setServerBaseUrl($value)
    {
        $this->serverBaseUrl = $value;
        return $this;
    }

    public function getServerBaseUrl()
    {
        return $this->serverBaseUrl;
    }

    // ----------------------------------------

    public function setServerHostName($value)
    {
        $this->serverHostName = $value;
        return $this;
    }

    public function getServerHostName()
    {
        return $this->serverHostName;
    }

    // ----------------------------------------

    /**
     * @param boolean $tryToResendOnError
     * @return $this
     */
    public function setTryToResendOnError($tryToResendOnError)
    {
        $this->tryToResendOnError = $tryToResendOnError;
        return $this;
    }

     /**
      * @return boolean
      */
    public function isTryToResendOnError()
    {
        return $this->tryToResendOnError;
    }

    // ----------------------------------------

    /**
     * @param boolean $tryToSwitchEndpointOnError
     * @return $this
     */
    public function setTryToSwitchEndpointOnError($tryToSwitchEndpointOnError)
    {
        $this->tryToSwitchEndpointOnError = $tryToSwitchEndpointOnError;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isTryToSwitchEndpointOnError()
    {
        return $this->tryToSwitchEndpointOnError;
    }

    // ########################################
}
