<?php

namespace ModernRetail\ApiOrders\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Cron extends Command
{


    protected function configure()
    {
        $this->setName('apiorders:cron');
        $this->setDescription('Send orders,invoices and creditmemos to (Cron emulation)');
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $objectManager->get('ModernRetail\ApiOrders\Cron\Export')->execute();


    }



}