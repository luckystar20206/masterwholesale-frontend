<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Magento\Config;

use Ess\M2ePro\Model\AbstractModel;
use Ess\M2ePro\Model\Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Mutable
 * @package Ess\M2ePro\Model\Magento\Config
 */
class Mutable extends AbstractModel
{
    /** @var \Magento\Framework\App\Config\ScopeCodeResolver */
    private $scopeCodeResolver;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\Framework\App\Config\ReinitableConfigInterface */
    private $storeConfig;

    //########################################

    public function __construct(
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ReinitableConfigInterface $storeConfig,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->storeConfig = $storeConfig;

        parent::__construct($helperFactory, $modelFactory, $data);
    }

    //########################################

    public function setValue(
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($this->isCanBeUsed()) {
            $this->helperFactory->getObject('Data_Cache_Runtime')->setValue(
                $this->preparePath($path, $scope, $scopeCode),
                $value,
                ['app_config_overrides']
            );
            return $this;
        }

        $this->storeConfig->setValue($path, $value, $scope, $scopeCode);
        return $this;
    }

    public function getValue(
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($this->isCanBeUsed()) {
            return $this->helperFactory->getObject('Data_Cache_Runtime')->getValue(
                $this->preparePath($path, $scope, $scopeCode)
            );
        }

        return $this->storeConfig->getValue($path, $scope, $scopeCode);
    }

    public function unsetValue(
        $path,
        $originalValue,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($this->isCanBeUsed()) {
            $this->helperFactory->getObject('Data_Cache_Runtime')->removeValue(
                $this->preparePath($path, $scope, $scopeCode)
            );
            return $this;
        }

        $this->storeConfig->setValue($path, $originalValue, $scope, $scopeCode);
        return $this;
    }

    //----------------------------------------

    public function clear()
    {
        if ($this->isCanBeUsed()) {
            $this->helperFactory->getObject('Data_Cache_Runtime')->removeTagValues('app_config_overrides');
            return $this;
        }

        throw new Exception('Unable to clear values. Must be cleared one by one.');
    }

    //########################################

    public function isCanBeUsed()
    {
        return version_compare($this->getHelper('Magento')->getVersion(), '2.1.2', '>');
    }

    //########################################

    /*
     * Copied from \Magento\Framework\App\Config.php
     */
    private function preparePath($path, $scope, $scopeCode)
    {
        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }

        $configPath = $scope;
        if ($scope !== 'default') {
            if (is_numeric($scopeCode) || $scopeCode === null) {
                $scopeCode = $this->getScopeCodeResolver()->resolve($scope, $scopeCode);
            } elseif ($scopeCode instanceof \Magento\Framework\App\ScopeInterface) {
                $scopeCode = $scopeCode->getCode();
            }
            if ($scopeCode) {
                $configPath .= '/' . $scopeCode;
            }
        }
        if ($path) {
            $configPath .= '/' . $path;
        }

        return $configPath;
    }

    //########################################

    private function getScopeCodeResolver()
    {
        if ($this->scopeCodeResolver === null) {
            $this->scopeCodeResolver = $this->objectManager->get(
                \Magento\Framework\App\Config\ScopeCodeResolver::class
            );
        }

        return $this->scopeCodeResolver;
    }

    //########################################
}
