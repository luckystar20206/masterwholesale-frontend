<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Helper;

class Extension extends \Magento\Framework\App\Helper\AbstractHelper
{

    public $moduleConfigList = null;

    public $moduleList = null;

    /**
     *
     * @var \Magento\Framework\Module\ModuleList
     */
    private $magentoModuleList;

    public function __construct(
        \Magento\Framework\Module\ModuleList $magentoModuleList,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->magentoModuleList = $magentoModuleList;
        parent::__construct($context);
    }

    public function getModuleList()
    {
        if ($this->moduleList === null) {
            $moduleList = [];
            foreach ($this->getModuleConfigList() as $moduleCode => $moduleConfig) {
                $moduleList[$moduleCode] = [
                    'name'    => $moduleConfig['name'] ?: $moduleCode,
                    'version' => $moduleConfig['setup_version'],
                ];
            }
            $this->moduleList = $moduleList;
        }
        return $this->moduleList;
    }

    public function getModuleConfigList()
    {
        if ($this->moduleConfigList === null) {
            $moduleConfigList = $this->magentoModuleList->getAll();

            ksort($moduleConfigList);
            $moduleList = [];
            foreach ($moduleConfigList as $moduleCode => $moduleConfig) {
                if (!$this->canShowExtension($moduleCode, $moduleConfig)) {
                    continue;
                }
                $moduleList[strtolower($moduleCode).'_m2'] = $moduleConfig;
            }
            $this->moduleConfigList = $moduleList;
        }
        return $this->moduleConfigList;
    }

    /**
     * @param string $code
     * @param \Magento\Framework\App\Config\Element $config
     *
     * @return bool
     */
    private function canShowExtension($code, $config)
    {
        if (!$code || !$config) {
            return false;
        }

        if (!$this->isNekloExtension($code)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    private function isNekloExtension($code)
    {
        return (strstr($code, 'Neklo_') !== false);
    }
}
