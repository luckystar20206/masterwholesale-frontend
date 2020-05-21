<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer as LayerCatalog;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\CatalogSearch\Model\Layer\Filter\Category as AbstractFilter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigation\Helper\Data as LayerHelper;

/**
 * Class Category
 * @package Mageplaza\LayeredNavigation\Model\Layer\Filter
 */
class Category extends AbstractFilter
{
    /** @var \Mageplaza\LayeredNavigation\Helper\Data */
    protected $_moduleHelper;

    /** @var bool Is Filterable Flag */
    protected $_isFilter = false;

    /** @var \Magento\Framework\Escaper */
    private $escaper;

    /** @var  \Magento\Catalog\Model\Layer\Filter\DataProvider\Category */
    private $dataProvider;

    /**
     * Category constructor.
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param LayerCatalog $layer
     * @param DataBuilder $itemDataBuilder
     * @param Escaper $escaper
     * @param CategoryFactory $categoryDataProviderFactory
     * @param LayerHelper $moduleHelper
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        LayerCatalog $layer,
        DataBuilder $itemDataBuilder,
        Escaper $escaper,
        CategoryFactory $categoryDataProviderFactory,
        LayerHelper $moduleHelper,
        array $data = []
    )
    {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $categoryDataProviderFactory,
            $data
        );

        $this->escaper       = $escaper;
        $this->_moduleHelper = $moduleHelper;
        $this->dataProvider  = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * @inheritdoc
     */
    public function apply(RequestInterface $request)
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return parent::apply($request);
        }

        $categoryId = $request->getParam($this->_requestVar);
        if (empty($categoryId)) {
            return $this;
        }

        $categoryIds = [];
        foreach (explode(',', $categoryId) as $key => $id) {
            $this->dataProvider->setCategoryId($id);
            if ($this->dataProvider->isValid()) {
                $category = $this->dataProvider->getCategory();
                if ($request->getParam('id') != $id) {
                    $categoryIds[] = $id;
                    $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $id));
                }
            }
        }

        if (sizeof($categoryIds)) {
            $this->_isFilter = true;
            $this->getLayer()->getProductCollection()->addLayerCategoryFilter($categoryIds);
        }

        if ($parentCategoryId = $request->getParam('id')) {
            $this->dataProvider->setCategoryId($parentCategoryId);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _getItemsData()
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return parent::_getItemsData();
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_isFilter) {
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch('category_ids');
        }

        $optionsFacetedData = $productCollection->getFacetedData('category');
        $category           = $this->dataProvider->getCategory();
        $categories         = $category->getChildrenCategories();

        $collectionSize = $productCollection->getSize();

        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                $count = isset($optionsFacetedData[$category->getId()]) ? $optionsFacetedData[$category->getId()]['count'] : 0;
                if ($category->getIsActive()
                    && $this->_moduleHelper->getFilterModel()->isOptionReducesResults($this, $count, $collectionSize)
                ) {
                    $this->itemDataBuilder->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $count
                    );
                }
            }
        }

        return $this->itemDataBuilder->build();
    }
}
