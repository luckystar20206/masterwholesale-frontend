<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\ControlPanel\Inspection;

/**
 * Class Cron
 * @package Ess\M2ePro\Block\Adminhtml\ControlPanel\Inspection
 */
class Cron extends AbstractInspection
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelInspectionCron');
        // ---------------------------------------

        $this->setTemplate('control_panel/inspection/cron.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $modConfig = $this->getHelper('Module')->getConfig();

        $this->cronLastRunTime = 'N/A';
        $this->cronIsNotWorking = false;
        $this->cronCurrentRunner = ucwords(str_replace('_', ' ', $this->getHelper('Module\Cron')->getRunner()));
        $this->cronServiceAuthKey = $modConfig->getGroupValue('/cron/service/', 'auth_key');

        $baseDir = $this->getHelper('Client')->getBaseDirectory();
        $this->cronPhp = 'php -q '.$baseDir.DIRECTORY_SEPARATOR.'cron.php -mdefault 1';

        $baseUrl = $this->getHelper('Magento')->getBaseUrl();
        $this->cronGet = 'GET '.$baseUrl.'cron.php';

        $cronLastRunTime = $this->getHelper('Module\Cron')->getLastRun();
        if ($cronLastRunTime !== null) {
            $this->cronLastRunTime = $cronLastRunTime;
            $this->cronIsNotWorking = $this->getHelper('Module\Cron')->isLastRunMoreThan(12, true);
        }

        $cronServiceIps = [];

        for ($i = 1; $i < 100; $i++) {
            $serviceHostName = $modConfig->getGroupValue('/cron/service/', 'hostname_'.$i);

            if ($serviceHostName === null) {
                break;
            }

            $cronServiceIps[] = gethostbyname($serviceHostName);
        }

        $this->cronServiceIps = implode(', ', $cronServiceIps);

        $this->isMagentoCronDisabled    = (bool)(int)$modConfig->getGroupValue('/cron/magento/', 'disabled');
        $this->isControllerCronDisabled = (bool)(int)$modConfig->getGroupValue('/cron/service_controller/', 'disabled');
        $this->isPubCronDisabled        = (bool)(int)$modConfig->getGroupValue('/cron/service_pub/', 'disabled');

        return parent::_beforeToHtml();
    }

    //########################################

    public function isShownRecommendationsMessage()
    {
        if (!$this->getData('is_support_mode')) {
            return false;
        }

        if ($this->getHelper('Module\Cron')->isRunnerMagento()) {
            return true;
        }

        if ($this->getHelper('Module\Cron')->isRunnerService() && $this->cronIsNotWorking) {
            return true;
        }

        return false;
    }

    public function isShownServiceDescriptionMessage()
    {
        if (!$this->getData('is_support_mode')) {
            return false;
        }

        if ($this->getHelper('Module\Cron')->isRunnerService() && !$this->cronIsNotWorking) {
            return true;
        }

        return false;
    }

    //########################################
}
