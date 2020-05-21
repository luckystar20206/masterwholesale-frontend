<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Connector\Account\Update;

/**
 * Class EntityResponser
 * @package Ess\M2ePro\Model\Amazon\Connector\Account\Update
 */
class EntityResponser extends \Ess\M2ePro\Model\Amazon\Connector\Command\Pending\Responser
{
    // ########################################

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getResponseData();
        if (!isset($responseData['info'])) {
            return false;
        }

        return true;
    }

    protected function processResponseData()
    {
        $responseData = $this->getPreparedResponseData();

        /** @var $amazonAccount \Ess\M2ePro\Model\Amazon\Account */
        $amazonAccount = $this->getAccount()->getChildObject();

        $dataForUpdate = [
            'info' => $this->getHelper('Data')->jsonEncode($responseData['info'])
        ];

        $amazonAccount->addData($dataForUpdate)->save();
    }

    // ########################################

    /**
     * @return \Ess\M2ePro\Model\Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account', 'account_id');
    }

    // ########################################
}
