<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Helper\Component\Ebay;

/**
 * Class Category
 * @package Ess\M2ePro\Helper\Component\Ebay
 */
class Category extends \Ess\M2ePro\Helper\AbstractHelper
{
    const TYPE_EBAY_MAIN = 0;
    const TYPE_EBAY_SECONDARY = 1;
    const TYPE_STORE_MAIN = 2;
    const TYPE_STORE_SECONDARY = 3;

    const RECENT_MAX_COUNT = 20;

    protected $activeRecordFactory;
    protected $resourceConnection;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->activeRecordFactory = $activeRecordFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($helperFactory, $context);
    }

    //########################################

    public function getEbayCategoryTypes()
    {
        return [
            self::TYPE_EBAY_MAIN,
            self::TYPE_EBAY_SECONDARY
        ];
    }

    public function getStoreCategoryTypes()
    {
        return [
            self::TYPE_STORE_MAIN,
            self::TYPE_STORE_SECONDARY
        ];
    }

    //########################################

    public function getRecent($marketplaceOrAccountId, $categoryType, $excludeCategory = null)
    {
        $configPath = $this->getRecentConfigPath($categoryType);
        $allRecentCategories = $this->activeRecordFactory->getObjectLoaded(
            'Registry',
            '/ebay/category/recent/',
            'key',
            false
        );

        if ($allRecentCategories !== null) {
            $allRecentCategories = $allRecentCategories->getValueFromJson();
        }

        if (!isset($allRecentCategories[$configPath]) ||
            !isset($allRecentCategories[$configPath][$marketplaceOrAccountId])) {
            return [];
        }

        $recentCategories = $allRecentCategories[$configPath][$marketplaceOrAccountId];

        if (in_array($categoryType, $this->getEbayCategoryTypes())) {
            $categoryHelper = $this->getHelper('Component_Ebay_Category_Ebay');
        } else {
            $categoryHelper = $this->getHelper('Component_Ebay_Category_Store');
        }

        $categoryIds = (array)explode(',', $recentCategories);
        $result = [];
        foreach ($categoryIds as $categoryId) {
            if ($categoryId === $excludeCategory) {
                continue;
            }

            $path = $categoryHelper->getPath($categoryId, $marketplaceOrAccountId);
            if (empty($path)) {
                continue;
            }

            $result[] = [
                'id' => $categoryId,
                'path' => $path . ' (' . $categoryId . ')',
            ];
        }

        return $result;
    }

    public function addRecent($categoryId, $marketplaceOrAccountId, $categoryType)
    {
        $key = '/ebay/category/recent/';
        $configPath = $this->getRecentConfigPath($categoryType);

        /** @var $registryModel \Ess\M2ePro\Model\Registry */
        $registryModel = $this->activeRecordFactory->getObjectLoaded('Registry', $key, 'key', false);

        if ($registryModel === null) {
            $registryModel = $this->activeRecordFactory->getObject('Registry');
        } else {
            $allRecentCategories = $registryModel->getValueFromJson();
        }

        $categories = [];
        if (isset($allRecentCategories[$configPath][$marketplaceOrAccountId])) {
            $categories = (array)explode(',', $allRecentCategories[$configPath][$marketplaceOrAccountId]);
        }

        if (count($categories) >= self::RECENT_MAX_COUNT) {
            array_pop($categories);
        }

        array_unshift($categories, $categoryId);
        $categories = array_unique($categories);

        $allRecentCategories[$configPath][$marketplaceOrAccountId] = implode(',', $categories);
        $registryModel->addData([
            'key' => $key,
            'value' => $this->getHelper('Data')->jsonEncode($allRecentCategories)
        ])->save();
    }

    // ---------------------------------------

    protected function getRecentConfigPath($categoryType)
    {
        $configPaths = [
            self::TYPE_EBAY_MAIN       => '/ebay/main/',
            self::TYPE_EBAY_SECONDARY  => '/ebay/secondary/',
            self::TYPE_STORE_MAIN      => '/store/main/',
            self::TYPE_STORE_SECONDARY => '/store/secondary/',
        ];

        return $configPaths[$categoryType];
    }

    //########################################

    public function getSameTemplatesData($ids, $table, $modes)
    {
        $fields = [];

        foreach ($modes as $mode) {
            $fields[] = $mode.'_id';
            $fields[] = $mode.'_path';
            $fields[] = $mode.'_mode';
            $fields[] = $mode.'_attribute';
        }

        $select = $this->resourceConnection->getConnection()->select();
        $select->from($table, $fields);
        $select->where('id IN (?)', $ids);

        $templatesData = $select->query()->fetchAll(\PDO::FETCH_ASSOC);

        $resultData = reset($templatesData);

        if (!$resultData) {
            return [];
        }

        foreach ($modes as $i => $mode) {
            if (!$this->getHelper('Data')->theSameItemsInData($templatesData, array_slice($fields, $i*4, 4))) {
                $resultData[$mode.'_id'] = 0;
                $resultData[$mode.'_path'] = null;
                $resultData[$mode.'_mode'] = \Ess\M2ePro\Model\Ebay\Template\Category::CATEGORY_MODE_NONE;
                $resultData[$mode.'_attribute'] = null;
                $resultData[$mode.'_message'] = $this->getHelper('Module\Translation')->__(
                    'Please, specify a value suitable for all chosen Products.'
                );
            }
        }

        return $resultData;
    }

    public function fillCategoriesPaths(array &$data, \Ess\M2ePro\Model\Listing $listing)
    {
        $ebayCategoryHelper = $this->getHelper('Component_Ebay_Category_Ebay');
        $ebayStoreCategoryHelper = $this->getHelper('Component_Ebay_Category_Store');

        $temp = [
            'category_main'            => ['call' => [$ebayCategoryHelper,'getPath'],
                                                'arg'  => $listing->getMarketplaceId()],
            'category_secondary'       => ['call' => [$ebayCategoryHelper,'getPath'],
                                                'arg'  => $listing->getMarketplaceId()],
            'store_category_main'      => ['call' => [$ebayStoreCategoryHelper,'getPath'],
                                                'arg'  => $listing->getAccountId()],
            'store_category_secondary' => ['call' => [$ebayStoreCategoryHelper,'getPath'],
                                                'arg'  => $listing->getAccountId()],
        ];

        foreach ($temp as $key => $value) {
            if (!isset($data[$key.'_mode']) || !empty($data[$key.'_path'])) {
                continue;
            }

            if ($data[$key.'_mode'] == \Ess\M2ePro\Model\Ebay\Template\Category::CATEGORY_MODE_EBAY) {
                $data[$key.'_path'] = call_user_func($value['call'], $data[$key.'_id'], $value['arg']);
            }

            if ($data[$key.'_mode'] == \Ess\M2ePro\Model\Ebay\Template\Category::CATEGORY_MODE_ATTRIBUTE) {
                $attributeLabel = $this->getHelper('Magento\Attribute')->getAttributeLabel(
                    $data[$key.'_attribute'],
                    $listing->getStoreId()
                );
                $data[$key.'_path'] = 'Magento Attribute' . ' > ' . $attributeLabel;
            }
        }
    }

    //########################################

    public function getCategoryTitles()
    {
        $titles = [];

        $type = self::TYPE_EBAY_MAIN;
        $titles[$type] = $this->getHelper('Module\Translation')->__('eBay Catalog Primary Category');

        $type = self::TYPE_EBAY_SECONDARY;
        $titles[$type] = $this->getHelper('Module\Translation')->__('eBay Catalog Secondary Category');

        $type = self::TYPE_STORE_MAIN;
        $titles[$type] = $this->getHelper('Module\Translation')->__('Store Catalog Primary Category');

        $type = self::TYPE_STORE_SECONDARY;
        $titles[$type] = $this->getHelper('Module\Translation')->__('Store Catalog Secondary Category');

        return $titles;
    }

    public function getCategoryTitle($type)
    {
        $titles = $this->getCategoryTitles();

        if (isset($titles[$type])) {
            return $titles[$type];
        }

        return '';
    }

    //########################################
}
