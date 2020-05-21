<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\HealthStatus\Notification\Magento\System;

use Magento\Framework\Notification\MessageInterface;
use Ess\M2ePro\Model\HealthStatus\Task\Result;

/**
 * Class Message
 * @package Ess\M2ePro\Model\HealthStatus\Notification\Magento\System
 */
class Message extends \Ess\M2ePro\Model\AbstractModel implements MessageInterface
{
    //########################################

    /** @var \Ess\M2ePro\Model\HealthStatus\CurrentStatus */
    protected $currentStatus;

    /** @var \Ess\M2ePro\Model\HealthStatus\Notification\Settings */
    protected $notificationSettings;

    //########################################

    public function __construct(
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        parent::__construct($helperFactory, $modelFactory);

        $this->currentStatus        = $this->modelFactory->getObject('HealthStatus\CurrentStatus');
        $this->notificationSettings = $this->modelFactory->getObject('HealthStatus_Notification_Settings');
    }

    //########################################

    public function getIdentity()
    {
        if ($this->helperFactory->getObject('Module\Maintenance')->isEnabled()) {
            return 'm2epro-health-status-notification';
        }

        return sha1('m2epro-health-status-' . $this->notificationSettings->getLevel());
    }

    public function isDisplayed()
    {
        if ($this->helperFactory->getObject('Module\Maintenance')->isEnabled()) {
            return false;
        }

        if (!$this->notificationSettings->isModeMagentoSystemNotification()) {
            return false;
        }

        if ($this->currentStatus->get() < $this->notificationSettings->getLevel()) {
            return false;
        }

        return true;
    }

    public function getText()
    {
        $messageBuilder = $this->modelFactory->getObject('HealthStatus_Notification_MessageBuilder');
        return $messageBuilder->build();
    }

    public function getSeverity()
    {
        switch ($this->currentStatus->get()) {
            case Result::STATE_NOTICE:
                return \Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE;

            case Result::STATE_WARNING:
                return \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR;

            default:
            case Result::STATE_CRITICAL:
                return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
        }
    }

    //########################################
}
