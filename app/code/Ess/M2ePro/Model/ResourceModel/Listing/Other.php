<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Listing;

/**
 * Class Other
 * @package Ess\M2ePro\Model\ResourceModel\Listing
 */
class Other extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Component\Parent\AbstractModel
{
    protected $synchronizationConfig;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\Config\Manager\Synchronization $synchronizationConfig,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Factory $parentFactory,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        $this->synchronizationConfig = $synchronizationConfig;
        parent::__construct(
            $helperFactory,
            $activeRecordFactory,
            $parentFactory,
            $context,
            $connectionName
        );
    }

    //########################################

    public function _construct()
    {
        $this->_init('m2epro_listing_other', 'id');
    }

    //########################################

    public function getItemsByProductId($productId, array $filters = [])
    {
        $cacheKey   = __METHOD__.$productId.sha1($this->getHelper('Data')->jsonEncode($filters));
        $cacheValue = $this->getHelper('Data_Cache_Runtime')->getValue($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $select = $this->getConnection()
            ->select()
            ->from(
                $this->getMainTable(),
                ['id','component_mode']
            )
            ->where("`product_id` IS NOT NULL AND `product_id` = ?", (int)$productId);

        if (!empty($filters)) {
            foreach ($filters as $column => $value) {
                $select->where('`'.$column.'` = ?', $value);
            }
        }

        $result = [];

        foreach ($select->query()->fetchAll() as $item) {
            $result[] = $this->parentFactory->getObjectLoaded(
                $item['component_mode'],
                'Listing\Other',
                (int)$item['id']
            );
        }

        $this->getHelper('Data_Cache_Runtime')->setValue($cacheKey, $result);

        return $result;
    }

    //########################################

    public function getChangedItems(
        array $attributes,
        $componentMode = null,
        $withStoreFilter = false
    ) {
        if (count($attributes) <= 0) {
            return [];
        }

        $productsChangesTable = $this->activeRecordFactory->getObject('ProductChange')->getResource()->getMainTable();

        $limit = (int)$this->synchronizationConfig->getGroupValue(
            '/settings/product_change/',
            'max_count_per_one_time'
        );

        $select = $this->getConnection()
                       ->select()
                       ->from($productsChangesTable, '*')
                       ->order(['id ASC'])
                       ->limit($limit);

        $select = $this->getConnection()
                       ->select()
                       ->from(
                           ['pc' => $select],
                           [
                                'changed_attribute'=>'attribute',
                                'changed_to_value'=>'value_new',
                            ]
                       )
                       ->join(
                           ['lo' => $this->getMainTable()],
                           '`pc`.`product_id` = `lo`.`product_id`',
                           'id'
                       )
                       ->where('`pc`.`action` = ?', (string)\Ess\M2ePro\Model\ProductChange::ACTION_UPDATE)
                       ->where("`pc`.`attribute` IN ('".implode("','", $attributes)."')");

        if ($withStoreFilter) {
            $whereStatement = '';

            if ($componentMode !== null) {
                $components = [$componentMode];
            } else {
                $components = $this->getHelper('Component')->getEnabledComponents();
            }

            foreach ($components as $component) {
                $accounts = $this->parentFactory->getObject($component, 'Account')->getCollection()->getItems();
                $marketplaces = $this->parentFactory->getObject($component, 'Marketplace')->getCollection()->getItems();

                foreach ($accounts as $account) {
                    /** @var $account \Ess\M2ePro\Model\Account */
                    foreach ($marketplaces as $marketplace) {
                        /** @var $marketplace \Ess\M2ePro\Model\Marketplace */
                        $whereStatement != '' && $whereStatement .= ' OR ';
                        $whereStatement .= ' ( `lo`.`account_id` = '.(int)$account->getId().' ';
                        $whereStatement .= ' AND `lo`.`marketplace_id` = '.(int)$marketplace->getId().' ';
                        $whereStatement .= ' AND `lo`.`component_mode` = \''.$component.'\' ';
                        $whereStatement .= ' AND `pc`.`store_id` = '.
                                        (int)$account->getChildObject()
                                            ->getRelatedStoreId($marketplace->getId()).' ) ';
                    }
                }
            }

            $whereStatement != '' && $select->where($whereStatement);
        }

        $componentMode !== null && $select->where("`lo`.`component_mode` = ?", (string)$componentMode);

        $results = [];

        foreach ($select->query()->fetchAll() as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($results);
    }

    //########################################
}
