<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Amazon\Template;

/**
 * Class ShippingOverride
 * @package Ess\M2ePro\Model\ResourceModel\Amazon\Template
 */
class ShippingOverride extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init('m2epro_amazon_template_shipping_override', 'id');
    }

    //########################################

    public function setSynchStatusNeed($newData, $oldData, $listingsProducts)
    {
        $listingsProductsIds = [];
        foreach ($listingsProducts as $listingProduct) {
            $listingsProductsIds[] = (int)$listingProduct['id'];
        }

        if (empty($listingsProductsIds)) {
            return;
        }

        if (!$this->isDifferent($newData, $oldData)) {
            return;
        }

        $templates = ['shippingTemplate'];

        $lpTable = $this->activeRecordFactory->getObject('Listing\Product')->getResource()->getMainTable();

        $this->getConnection()->update(
            $lpTable,
            [
                'synch_status' => \Ess\M2ePro\Model\Listing\Product::SYNCH_STATUS_NEED,
                'synch_reasons' => new \Zend_Db_Expr(
                    "IF(synch_reasons IS NULL,
                        '".implode(',', $templates)."',
                        CONCAT(synch_reasons,'".','.implode(',', $templates)."')
                    )"
                )
            ],
            ['id IN ('.implode(',', $listingsProductsIds).')']
        );
    }

    // ---------------------------------------

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = [
            $this->getIdFieldName(),
            'title',
            'create_date', 'update_date'
        ];

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField], $oldData[$ignoreField]);
        }

        !isset($newData['services']) && $newData['services'] = [];
        !isset($oldData['services']) && $oldData['services'] = [];

        foreach ($newData['services'] as $key => $newService) {
            unset($newData['services'][$key]['id'], $newData['services'][$key]['template_shipping_override_id']);
        }
        foreach ($oldData['services'] as $key => $oldService) {
            unset($oldData['services'][$key]['id'], $oldData['services'][$key]['template_shipping_override_id']);
        }

        ksort($newData);
        ksort($oldData);
        array_walk($newData['services'], 'ksort');
        array_walk($oldData['services'], 'ksort');

        $encodedNewData = $this->getHelper('Data')->jsonEncode($newData);
        $encodedOldData = $this->getHelper('Data')->jsonEncode($oldData);

        return sha1($encodedNewData) !== sha1($encodedOldData);
    }

    //########################################
}
