<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Plugin\StockItem\Magento\CatalogInventory\Model\Stock;

/**
 * Class Item
 * @package Ess\M2ePro\Plugin\StockItem\Magento\CatalogInventory\Model\Stock
 */
class Item extends \Ess\M2ePro\Plugin\AbstractPlugin
{
    protected $eventManager;

    //########################################

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        $this->eventManager = $eventManager;
        parent::__construct($helperFactory, $modelFactory);
    }

    //########################################

    public function canExecute()
    {
        if (!parent::canExecute()) {
            return false;
        }

        return version_compare($this->getHelper('Magento')->getVersion(), '2.2.0', '<');
    }

    //########################################

    public function aroundBeforeSave($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('beforeSave', $interceptor, $callback, $arguments);
    }

    // ---------------------------------------

    protected function processBeforeSave($interceptor, \Closure $callback, array $arguments)
    {
        $result = $callback(...$arguments);

        $this->eventManager->dispatch(
            'cataloginventory_stock_item_save_before',
            [
                'data_object' => $interceptor,
                'object' => $interceptor,
                'item' => $interceptor,
            ]
        );

        return $result;
    }

    //########################################

    public function aroundAfterSave($interceptor, \Closure $callback)
    {
        return $this->execute('afterSave', $interceptor, $callback);
    }

    // ---------------------------------------

    protected function processAfterSave($interceptor, \Closure $callback)
    {
        $result = $callback();

        $this->eventManager->dispatch(
            'cataloginventory_stock_item_save_after',
            [
                'data_object' => $interceptor,
                'object' => $interceptor,
                'item' => $interceptor,
            ]
        );

        return $result;
    }

    //########################################
}
