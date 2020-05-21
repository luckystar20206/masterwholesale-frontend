<?php
namespace ModernRetail\CancelEmails\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Test  extends Command{


    protected function configure()
    {
        $this->setName('cancelemails');
        $this->setDescription('cancelemails');
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();



        $emailSender = $objectManager->create('ModernRetail\CancelEmails\Model\EmailSender');

        $orderRepository = $objectManager->get('Magento\Sales\Api\OrderRepositoryInterface');

        $order = $orderRepository->get(570); 

        $emailSender->send($order);
        ci($emailSender);


    }
}