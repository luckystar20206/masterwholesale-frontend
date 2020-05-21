<?php

namespace ModernRetail\ApiOrders\Ui\Component\Listing\Column;

class MrApiSyncStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $_orderRepository;
    protected $_helper;
    protected $_storeManager;
    protected $_searchCriteria;
    protected $_queueFactory;

    protected $gridType = 'order';

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \ModernRetail\ApiOrders\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteria,
        \ModernRetail\ApiOrders\Model\Queue $_queueFactory,
        array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_searchCriteria = $criteria;
        $this->_queueFactory = $_queueFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->getGridType();
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getGridType()
    {
        return $this->gridType = $this->getConfiguration()['grid_type'];
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $collection = $this->_queueFactory->getCollection();
            $collection->addFieldToFilter('type', $this->gridType);
            foreach ($dataSource['data']['items'] as &$item) {
                $status = '';
                foreach ($collection as $queue) {
                    if ($queue->getEntityId() == $item['entity_id']) {
                        $status = $queue->getStatus();
                        break;
                    }
                }
                if ($status)
                    $item[$this->getData('name')] = $status;
                else
                    $item[$this->getData('name')] = 'no';
            }
        }
        return $dataSource;
    }

    public function prepare()
    {
        parent::prepare();
        $configName = $this->_helper::ENABLED_CONFIG[$this->gridType];
        $isEnabled = $this->_helper->isEnabled($this->getStoreId(), $configName);
        $this->_data['config']['componentDisabled'] = !$isEnabled ? true : false;
    }
}