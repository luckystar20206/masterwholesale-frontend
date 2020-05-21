<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Model\ResourceModel\Request\Grid;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends SearchResult implements SearchResultInterface
{

    /**
     * @var \Magento\Framework\Api\Search\AggregationInterface
     */
    public $aggregations;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param EavConfig $eavConfig
     * @param string $mainTable
     * @param string $resourceModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct( // @codingStandardsIgnoreLine
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        EavConfig $eavConfig,
        $mainTable = 'neklo_make_an_offer_request',
        $resourceModel = '\Neklo\MakeAnOffer\Model\ResourceModel\Request'
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    public function _initSelect()
    {
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('product_name', 'CPEV.value');
        $this->addFilterToMap('order_number', 'SO.increment_id');
        $this->addFilterToMap('created_at', 'main_table.created_at');

        parent::_initSelect();

        $entityColumnCPEV = $this->getConnection()
            ->tableColumnExists(
                $this->getTable('catalog_product_entity_varchar'),
                'entity_id'
            ) ? 'CPEV.entity_id' : 'CPEV.row_id';

        $entityColumnCPED = $this->getConnection()
            ->tableColumnExists(
                $this->getTable('catalog_product_entity_decimal'),
                'entity_id'
            ) ? 'CPED.entity_id' : 'CPED.row_id';

        $this->getSelect()
            ->joinLeft(
                ['CPED' => $this->getTable('catalog_product_entity_decimal')],
                "main_table.product_id = " . $entityColumnCPED . "
                 AND CPED.store_id = 0
                 AND CPED.attribute_id = " . $this->getPriceAttributeId(),
                ["actual_price" => "(CPED.value * main_table.product_qty)"]
            )
            ->joinLeft(
                ['CPEV' => $this->getTable('catalog_product_entity_varchar')],
                "main_table.product_id = " . $entityColumnCPEV . "
                 AND CPEV.store_id = 0
                 AND CPEV.attribute_id = " . $this->getNameAttributeId(),
                ["product_name" => "CPEV.value"]
            )
            ->joinLeft(
                ['SO' => $this->getTable('sales_order')],
                "main_table.order_id = SO.entity_id",
                [
                    "order_number"  => "SO.increment_id",
                    "order_date" => "SO.created_at"
                ]
            );
    }

    /**
     * Get price attribute id
     *
     * @return int
     */
    private function getPriceAttributeId()
    {
        $priceAttributeId = $this->eavConfig
            ->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                \Magento\Catalog\Api\Data\ProductInterface::PRICE
            )
            ->getAttributeId();
        return $priceAttributeId;
    }

    /**
     * Get name attribute id
     *
     * @return int
     */
    private function getNameAttributeId()
    {
        $priceAttributeId = $this->eavConfig
            ->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                \Magento\Catalog\Api\Data\ProductInterface::NAME
            )
            ->getAttributeId();
        return $priceAttributeId;
    }

    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
