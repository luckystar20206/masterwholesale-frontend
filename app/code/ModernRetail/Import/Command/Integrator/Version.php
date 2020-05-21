<?php
namespace ModernRetail\Import\Command\Integrator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Version extends Command
{
    public function __construct(
        \Magento\Framework\App\State $state,
        \ModernRetail\Import\Helper\Data $dataHelper,
        \ModernRetail\Import\Helper\Version $versionHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ResourceConnection $resource

    ){
        //$state->setAreaCode('adminhtml');
        $this->eventManager = $eventManager;
        $this->helper = $dataHelper;
        $this->resource = $resource;
     
        $this->versionHelper = $versionHelper;

        parent::__construct();
    }


    

    protected function configure()
    {
        $this->setName('integrator:version');
        $this->setDescription('Integrator Version: check and upgrade');
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($input->hasArgument("upgrade")) {

        }


        $currentVersion = $this->versionHelper->getCurrentVersion();
        $latestVersion = $this->versionHelper->getLatestVersion();
        $output->writeLn("Installed version: $currentVersion");
        $output->writeLn("Latest Available Version: $latestVersion");


        if ($this->versionHelper->isNeedUpgrade()){
            $output->writeLn("WARNING! Your integrator version is outdated. Please type 'php bin/magento integrator:version upgrade'");
        }

    }




    public function getInputList() {
        $inputList = [];
        $inputList[] = new InputArgument('upgrade', InputArgument::OPTIONAL, 'Upgrade integrator to latest', null);
        $inputList[] = new InputArgument('current', InputArgument::OPTIONAL, 'Check Current version', null);
        $inputList[] = new InputArgument('latest', InputArgument::OPTIONAL, 'Get Latest available version', null);
        return $inputList;
    }

}