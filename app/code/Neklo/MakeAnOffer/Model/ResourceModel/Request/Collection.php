<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


// @codingStandardsIgnoreFile

namespace Neklo\MakeAnOffer\Model\ResourceModel\Request;

use Magento\Eav\Model\Config;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected $_eventPrefix = 'neklo_make_an_offer_request_collection';

    protected $_eventObject = 'request_collection';

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param Context $context
     * @param Config $eavConfig
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Config $eavConfig,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Neklo\MakeAnOffer\Model\Request::class,
            \Neklo\MakeAnOffer\Model\ResourceModel\Request::class
        );
    }

    public function _initSelect()
    {
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
                "main_table.product_id = " . $entityColumnCPED ."
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
}
