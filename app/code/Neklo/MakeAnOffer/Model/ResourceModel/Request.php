<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


// @codingStandardsIgnoreFile

namespace Neklo\MakeAnOffer\Model\ResourceModel;

use Magento\Eav\Model\Config;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Request extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
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
        Context $context,
        Config $eavConfig,
        $connectionName = null
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('neklo_make_an_offer_request', 'id');
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

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

        $select->joinLeft(
            ['CPED' => $this->getTable('catalog_product_entity_decimal')],
            $this->getTable('neklo_make_an_offer_request') . ".product_id = " . $entityColumnCPED . "
                 AND CPED.store_id = 0
                 AND CPED.attribute_id = " . $this->getPriceAttributeId(),
            ["actual_price" => "(CPED.value * ". $this->getTable('neklo_make_an_offer_request') . ".product_qty)"]
        )
            ->joinLeft(
                ['CPEV' => $this->getTable('catalog_product_entity_varchar')],
                $this->getTable('neklo_make_an_offer_request') .  ".product_id = " . $entityColumnCPEV . "
                 AND CPEV.store_id = 0
                 AND CPEV.attribute_id = " . $this->getNameAttributeId(),
                ["product_name" => "CPEV.value"]
            )
            ->joinLeft(
                ['SO' => $this->getTable('sales_order')],
                $this->getTable('neklo_make_an_offer_request') . ".order_id = SO.entity_id",
                [
                    "order_number"  => "SO.increment_id",
                    "order_date" => "SO.created_at"
                ]
            );

        return $select;
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
