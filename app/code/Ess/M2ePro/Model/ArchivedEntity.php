<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model;

/**
 * Class ArchivedEntity
 * @package Ess\M2ePro\Model
 */
class ArchivedEntity extends \Ess\M2ePro\Model\ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('Ess\M2ePro\Model\ResourceModel\ArchivedEntity');
    }

    //########################################

    public function deleteProcessingLocks($tag = false, $processingId = false)
    {
        return null;
    }

    //########################################

    public function getName()
    {
        return $this->getData('name');
    }

    public function getOriginId()
    {
        return $this->getData('origin_id');
    }

    public function getOriginData()
    {
        return $this->getData('data');
    }

    //########################################
}
