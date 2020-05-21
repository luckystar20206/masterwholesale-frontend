<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Marketplace;

/**
 * Class Details
 * @package Ess\M2ePro\Model\Amazon\Marketplace
 */
class Details extends \Ess\M2ePro\Model\AbstractModel
{
    private $marketplaceId = null;

    private $productData = [];

    private $resourceConnection;

    //########################################

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($helperFactory, $modelFactory);
    }

    //########################################

    /**
     * @param $marketplaceId
     * @return $this
     * @throws \Ess\M2ePro\Model\Exception
     */
    public function setMarketplaceId($marketplaceId)
    {
        if ($this->marketplaceId === $marketplaceId) {
            return $this;
        }

        $this->marketplaceId = $marketplaceId;
        $this->load();

        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getProductData()
    {
        return $this->productData;
    }

    /**
     * @param $productDataNick
     * @return array
     */
    public function getVariationThemes($productDataNick)
    {
        if (!isset($this->productData[$productDataNick])) {
            return [];
        }

        return (array)$this->productData[$productDataNick]['variation_themes'];
    }

    /**
     * @param $productDataNick
     * @param $theme
     * @return array
     */
    public function getVariationThemeAttributes($productDataNick, $theme)
    {
        $themes = $this->getVariationThemes($productDataNick);
        return !empty($themes[$theme]['attributes']) ? $themes[$theme]['attributes'] : [];
    }

    //########################################

    private function load()
    {
        if ($this->marketplaceId === null) {
            throw new \Ess\M2ePro\Model\Exception('Marketplace was not set.');
        }

        $connRead = $this->resourceConnection->getConnection();
        $table    = $this->getHelper('Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_dictionary_marketplace');

        $data = $connRead->select()
            ->from($table)
            ->where('marketplace_id = ?', (int)$this->marketplaceId)
            ->query()
            ->fetch();

        if ($data === false) {
            throw new \Ess\M2ePro\Model\Exception('Marketplace not found or not synchronized');
        }

        $this->productData    = $this->getHelper('Data')->jsonDecode($data['product_data']);
    }

    //########################################
}
