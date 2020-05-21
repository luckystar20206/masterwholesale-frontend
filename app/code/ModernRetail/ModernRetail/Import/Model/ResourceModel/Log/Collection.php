<?php
namespace ModernRetail\Import\Model\ResourceModel\Log;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'mr_import_log_collection';
    protected $_eventObject = 'log_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ModernRetail\Import\Model\Log', 'ModernRetail\Import\Model\ResourceModel\Log');
    }

}
