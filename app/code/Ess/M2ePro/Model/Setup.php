<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model;

/**
 * Class Setup
 * @package Ess\M2ePro\Model
 */
class Setup extends ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('Ess\M2ePro\Model\ResourceModel\Setup');
    }

    //########################################

    public function getVersionFrom()
    {
        return $this->getData('version_from');
    }

    public function getVersionTo()
    {
        return $this->getData('version_to');
    }

    public function isBackuped()
    {
        return (bool)$this->getData('is_backuped');
    }

    public function isCompleted()
    {
        return (bool)$this->getData('is_completed');
    }

    public function getProfilerData()
    {
        return (array)$this->getSettings('profiler_data');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    //########################################
}
