<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model;

/**
 * Class Registry
 * @package Ess\M2ePro\Model
 */
class Registry extends \Ess\M2ePro\Model\ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('Ess\M2ePro\Model\ResourceModel\Registry');
    }

    //########################################

    public function getKey()
    {
        return $this->getData('key');
    }

    public function getValue()
    {
        return $this->getData('value');
    }

    //########################################

    public function getValueFromJson()
    {
        return $this->getId() === null ?  [] : $this->getHelper('Data')->jsonDecode($this->getValue());
    }

    //########################################
}
