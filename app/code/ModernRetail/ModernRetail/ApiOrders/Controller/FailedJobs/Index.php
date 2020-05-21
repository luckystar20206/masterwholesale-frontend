<?php

namespace ModernRetail\ApiOrders\Controller\FailedJobs;

class Index extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->_resource = $resource;
        $this->connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }


    public function execute()
    {
        try {

            $result = $this->collectData();
            $data = [];
            foreach ($result as $k => $v) {
                $data[$k] = $v;
            }

            if (count($data) > 0) {
                $result = $this->resultJsonFactory->create()->setData(['status' => 'OK', 'data' => $data]);
            } else {
                $result = $this->resultJsonFactory->create()->setData(['status' => 'EMPTY']);
            }

        } catch (\Exception $e) {
            return $this->resultJsonFactory->create()->setData(['status' => 'ERROR', 'data' => $e->getMessage()]);
        }
        return $result;
    }

    public function collectData(){
        $tables = [];
        $interval = 24;
        $connection = $this->connection;

        $mr_api_queue = $this->_resource->getTableName('mr_api_queue');
        $sales_order = $this->_resource->getTableName('sales_order');
        $sales_invoice = $this->_resource->getTableName('sales_invoice');
        $sales_shipment = $this->_resource->getTableName('sales_shipment');
        $sales_creditmemo = $this->_resource->getTableName('sales_creditmemo');
        $tables = [$sales_order,$sales_invoice,$sales_shipment,$sales_creditmemo];


        $result = [];

        foreach ($tables as $table){
            $sql = "select * from (SELECT so.entity_id,  mq.type,  so.increment_id, so.updated_at, mq.status, mq.response FROM $table as so 
                  LEFT JOIN $mr_api_queue as mq ON so.entity_id = mq.entity_id 
                WHERE so.updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                      AND so.updated_at > DATE_SUB(NOW(), INTERVAL $interval HOUR)) as tt 
                where tt.status IS NULL OR tt.status != 'complete'";

            $fetch = $connection->fetchAll($sql);

            if(count($fetch) < 1){
                continue;
            }

            $result[$table] = $fetch;
        }

        return $result;

    }

}