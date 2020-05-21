<?php
namespace ModernRetail\Import\Command\Integrator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EraseSpecialPrices extends Command
{


    public function __construct(
        \Magento\Framework\App\State $state,
        \ModernRetail\Import\Helper\Data $dataHelper,
        \ModernRetail\Import\Helper\SpecialPrice $specialPriceHelper

    ){
        //$state->setAreaCode('adminhtml');

        $this->helper = $dataHelper;

        $this->specialPriceHelper = $specialPriceHelper;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('integrator:erase_special_prices');
        $this->setDescription('Remove special_prices if dates is old');
        //$this->setDefinition($this->getInputList());
        parent::configure();
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->specialPriceHelper->cleanSpecialPriceForAllProducts();
    }





}