<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ModernRetail\ApiOrders\Ui\Component;

/**
 * Mass action UI component.
 *
 * @api
 * @since 100.0.2
 */
class MassAction extends \Magento\Ui\Component\MassAction
{
    protected $_helper;
    protected $_storeManager;

    public function __construct(
        \ModernRetail\ApiOrders\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        $config = $this->getConfiguration();
        $configName = $this->_helper::ENABLED_CONFIG[$config['grid_type']];
        $isEnabled = $this->_helper->isEnabled($this->getStoreId(), $configName);
        if (!$isEnabled)
            $config['actionDisable'] = true;
        else
            $config['actionDisable'] = false;
        $this->setData('config', $config);
        parent::prepare();
    }
}
