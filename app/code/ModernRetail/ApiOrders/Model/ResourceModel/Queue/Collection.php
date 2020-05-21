<?php


namespace ModernRetail\ApiOrders\Model\ResourceModel\Queue;



class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'queue_row_id';
    protected $_eventPrefix = 'mr_api_queue_collection';
    protected $_eventObject = 'mr_api_queue_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ModernRetail\ApiOrders\Model\Queue', 'ModernRetail\ApiOrders\Model\ResourceModel\Queue');
    }

}
