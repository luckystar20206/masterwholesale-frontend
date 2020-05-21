<?php
namespace ModernRetail\CancelOrderItems\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Cancel  extends Command{


    protected function configure()
    {
        $this->setName('cancel_orders');
        $this->setDescription('cancel_orders');
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $sales_order = $resource->getTableName('sales_order');
        $sales_order_item = $resource->getTableName('sales_order_item');

        $sql = " 
            update $sales_order_item 
            set qty_canceled = qty_pre_canceled
            where 
            order_id in (
            select order_id from ( 
             select  order_id, sum((qty_pre_canceled + qty_invoiced)) as calculated, sum(qty_ordered) as qty_ordered from $sales_order_item  where qty_pre_canceled > 0 and qty_canceled =0  group by order_id) as tt where tt.calculated = tt.qty_ordered
            )";
 

        $result = $connection->query($sql)->fetchAll();

    }
}