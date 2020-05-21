<?php

namespace ModernRetail\ApiOrders\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Export extends Command
{

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \ModernRetail\ApiOrders\Helper\Data $apiOrdersHelper,
        \ModernRetail\ApiOrders\Model\Queue $apiOrdersQueue

    )
    {
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->apiOrdersHelper = $apiOrdersHelper;
        $this->queue = $apiOrdersQueue;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('apiorders');
        $this->setDescription('Send orders,invoices and creditmemos to api');
        $this->setDefinition($this->getInputList());
        parent::configure();
    }

    public function getInputList() {
        $inputList = [];
        $inputList[] = new InputArgument('type', InputArgument::REQUIRED, 'order, invoice, creditmemo, shipment', null);
        $inputList[] = new InputArgument('id', InputArgument::REQUIRED, 'entity_id', null);
        #   $inputList[] = new InputOption('mylimit', null, InputOption::VALUE_OPTIONAL, 'Collection Limit as Option', 100);
        return $inputList;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $type = $input->getArgument("type");
        $type = strtolower($type);
        $id = $input->getArgument('id');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');

        switch ($type) {
            case 'order':
               $order =$this->orderRepository->get($id);
               if(!$order->getId()){
                   throw  new \Exception('Order '.$id." not found");
               }
               $queue = $this->queue->add("order",$id);
               $result = $queue->send();

               break;
            case 'invoice':

                break;
            case 'shipment':

                break;
            case 'creditmemo':

                break;
            case 'all':


                break;
        }


    }





}