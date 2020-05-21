<?php

namespace ModernRetail\Import\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;


class UpgradeData implements UpgradeDataInterface {

    public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context )
    {

        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.9.5', '>=')){


            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
            $version = $productMetadata->getVersion();

           if ($version >= '2.3.0') {

                /**
                 * RemoteAbstract for Magento ver 2.3.0 and greater uses features of php7.1
                 *


                $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
                $rootPath = $directory->getRoot();

                $updatedFile = $rootPath . '/ModernRetail/Import/Controller/RemoteAbstract.log';
                $standartFile = $rootPath . '/ModernRetail/Import/Controller/RemoteAbstract.php';

                try {
                    $handle = @fopen($updatedFile, "r");
                    $probationer = @fopen($standartFile, "r");

                    if ($handle && $probationer) {
                        $current = file_get_contents($updatedFile);
                        $rewrite = file_put_contents($standartFile, $current);
                    } else {
                        throw new \Exception;
                    }
                } catch (\Exception $e) {
                    var_dump($e->getMessage());
                }
                 *  */
            }
        }


        

        $setup->endSetup();
    }
}