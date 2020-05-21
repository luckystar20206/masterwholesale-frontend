<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\Component\Parent;

/**
 * Class AbstractModel
 * @package Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\Component\Parent
 */
abstract class AbstractModel extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\Component\AbstractModel
{
    protected $childMode = null;

    protected $activeRecordFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        $childMode = null
    ) {
        if ($childMode !== null) {
            $this->setChildMode($childMode);
        }

        parent::__construct(
            $helperFactory,
            $activeRecordFactory,
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    //########################################

    public function setChildMode($mode)
    {
        $mode = strtolower((string)$mode);
        $mode && $this->childMode = $mode;
        return $this;
    }

    public function getChildMode()
    {
        return $this->childMode;
    }

    //########################################

    protected function _initSelect()
    {
        $temp = parent::_initSelect();

        if ($this->childMode === null) {
            return $temp;
        }

        /** @var $resource \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Component\Parent\AbstractModel */
        $resource = $this->getResource();

        $componentTable = $resource->getChildTable($this->childMode);
        $componentPk = $resource->getChildPrimary($this->childMode);

        $this->getSelect()->join(
            ['second_table'=>$componentTable],
            "`second_table`.`".$componentPk."` = `main_table`.`id`"
        );
        $this->getSelect()->where("`main_table`.`component_mode` = '".$this->childMode."'");

        return $temp;
    }

    //########################################

    public function addItem(\Magento\Framework\DataObject $item)
    {
        /** @var $item \Ess\M2ePro\Model\ActiveRecord\Component\Parent\AbstractModel */

        if ($this->childMode === null) {
            return parent::addItem($item);
        }

        $item->setChildMode($this->childMode);

        if ($item->getId() === null) {
            return parent::addItem($item);
        }

        return parent::addItem($this->prepareChildObject($item));
    }

    // ---------------------------------------

    public function getFirstItem()
    {
        /** @var $item \Ess\M2ePro\Model\ActiveRecord\Component\Parent\AbstractModel */
        $item = parent::getFirstItem();

        if ($this->childMode === null) {
            return $item;
        }

        $item->setChildMode($this->childMode);

        return $item;
    }

    public function getLastItem()
    {
        /** @var $item \Ess\M2ePro\Model\ActiveRecord\Component\Parent\AbstractModel */
        $item = parent::getLastItem();

        if ($this->childMode === null) {
            return $item;
        }

        $item->setChildMode($this->childMode);

        return $item;
    }

    //########################################

    protected function prepareChildObject(\Ess\M2ePro\Model\ActiveRecord\Component\Parent\AbstractModel $object)
    {
        $data = $object->getData();
        $object->unsetData();

        /** @var \Ess\M2ePro\Model\ActiveRecord\Component\Child\AbstractModel $childObject */
        $modelName = str_replace('Ess\M2ePro\Model', ucwords($this->childMode), $this->_model);
        $childObject = $this->activeRecordFactory->getObject($modelName);

        /** @var $resource \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Component\Parent\AbstractModel */
        $resource = $this->getResource();

        $childColumnsData = $this->getConnection()->describeTable($resource->getChildTable($this->childMode));
        foreach ($childColumnsData as $columnData) {
            if (!isset($data[$columnData['COLUMN_NAME']])) {
                continue;
            }

            $childObject->setData($columnData['COLUMN_NAME'], $data[$columnData['COLUMN_NAME']]);
            unset($data[$columnData['COLUMN_NAME']]);
        }

        // setting parent data + data from additionally joined tables
        $object->setData($data);
        $object->setOrigData();

        $childObject->setParentObject($object);
        $childObject->setOrigData();

        $object->setChildObject($childObject);

        return $object;
    }

    //########################################
}
