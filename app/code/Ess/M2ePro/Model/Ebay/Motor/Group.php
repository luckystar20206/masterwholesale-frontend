<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Motor;

/**
 * Class Group
 * @package Ess\M2ePro\Model\Ebay\Motor
 */
class Group extends \Ess\M2ePro\Model\ActiveRecord\Component\AbstractModel
{
    const MODE_ITEM     = 1;
    const MODE_FILTER   = 2;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('Ess\M2ePro\Model\ResourceModel\Ebay\Motor\Group');
    }

    //########################################

    public function delete()
    {
        if (!parent::delete()) {
            return false;
        }

        $connection = $this->getResource()->getConnection();
        $filterGroupRelation = $this->getHelper('Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_motor_filter_to_group');
        $connection->delete($filterGroupRelation, ['group_id = ?' => $this->getId()]);

        return true;
    }

    //########################################

    /**
     * @return int
     */
    public function getTitle()
    {
        return (int)$this->getData('title');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isModeItem()
    {
        return $this->getMode() == self::MODE_ITEM;
    }

    /**
     * @return bool
     */
    public function isModeFilter()
    {
        return $this->getMode() == self::MODE_FILTER;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getType()
    {
        return (int)$this->getData('type');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isTypeEpid()
    {
        return in_array($this->getType(), [
            \Ess\M2ePro\Helper\Component\Ebay\Motors::TYPE_EPID_MOTOR,
            \Ess\M2ePro\Helper\Component\Ebay\Motors::TYPE_EPID_UK,
            \Ess\M2ePro\Helper\Component\Ebay\Motors::TYPE_EPID_DE,
        ]);
    }

    /**
     * @return bool
     */
    public function isTypeKtype()
    {
        return $this->getType() == \Ess\M2ePro\Helper\Component\Ebay\Motors::TYPE_KTYPE;
    }

    //########################################

    public function getItemsData()
    {
        return $this->getData('items_data');
    }

    //########################################

    public function getItems()
    {
        $data = $this->getHelper('Component_Ebay_Motors')->parseAttributeValue($this->getItemsData());

        return $data['items'];
    }

    public function getFiltersIds()
    {
        $connection = $this->getResource()->getConnection();
        $table = $this->getHelper('Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_motor_filter_to_group');

        $select = $connection->select();
        $select->from(['emftg' => $table], ['filter_id'])
               ->where('group_id IN (?)', $this->getId());

        return $connection->fetchCol($select);
    }

    //########################################

    public function getNote()
    {
        return $this->getData('note');
    }

    //########################################
}
