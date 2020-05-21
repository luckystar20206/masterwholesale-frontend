<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Template;

use Ess\M2ePro\Controller\Adminhtml\Context;

/**
 * Class ProductTaxCode
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Template
 */
abstract class ProductTaxCode extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Template
{
    protected $transactionFactory;

    //########################################

    public function __construct(
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        Context $context
    ) {
        $this->transactionFactory = $transactionFactory;
        parent::__construct($amazonFactory, $context);
    }

    //########################################

    protected function filterLockedProducts($productsIdsParam)
    {
        $table = $this->getHelper('Module_Database_Structure')->getTableNameWithPrefix('m2epro_processing_lock');

        $productsIds = [];
        $productsIdsParam = array_chunk($productsIdsParam, 1000);
        foreach ($productsIdsParam as $productsIdsParamChunk) {
            $select = $this->resourceConnection->getConnection()->select();
            $select->from(['lo' => $table], ['object_id'])
                ->where('model_name = "M2ePro/Listing_Product"')
                ->where('object_id IN (?)', $productsIdsParamChunk)
                ->where('tag IS NOT NULL');

            $lockedProducts = $this->resourceConnection->getConnection()->fetchCol($select);

            foreach ($lockedProducts as $id) {
                $key = array_search($id, $productsIdsParamChunk);
                if ($key !== false) {
                    unset($productsIdsParamChunk[$key]);
                }
            }

            $productsIds = array_merge($productsIds, $productsIdsParamChunk);
        }

        return $productsIds;
    }

    protected function setProductTaxCodeTemplateForProducts($productsIds, $templateId)
    {
        if (empty($productsIds)) {
            return;
        }

        $collection = $this->amazonFactory->getObject('Listing\Product')->getCollection();
        $collection->addFieldToFilter('id', ['in' => $productsIds]);
        // ---------------------------------------

        if ($collection->getSize() == 0) {
            return;
        }

        /** @var \Magento\Framework\DB\Transaction $transaction */
        $transaction = $this->transactionFactory->create();
        $oldSnapshots = [];

        try {
            foreach ($collection->getItems() as $listingProduct) {
                /**@var \Ess\M2ePro\Model\Listing\Product $listingProduct */

                $oldSnapshots[$listingProduct->getId()] = array_merge(
                    $listingProduct->getDataSnapshot(),
                    $listingProduct->getChildObject()->getDataSnapshot()
                );

                $listingProduct->getChildObject()->setData('template_product_tax_code_id', $templateId);
                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (\Exception $e) {
            $oldSnapshots = false;
        }

        if (!$oldSnapshots) {
            return;
        }

        foreach ($collection->getItems() as $listingProduct) {
            /**@var \Ess\M2ePro\Model\Listing\Product $listingProduct */

            $listingProduct->getChildObject()->setSynchStatusNeed(
                array_merge(
                    $listingProduct->getDataSnapshot(),
                    $listingProduct->getChildObject()->getDataSnapshot()
                ),
                $oldSnapshots[$listingProduct->getId()]
            );
        }
    }

    protected function runProcessorForParents($productsIds)
    {
        $tableAmazonListingProduct = $this->getHelper('Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        $select = $this->resourceConnection->getConnection()->select();
        $select->from(['alp' => $tableAmazonListingProduct], ['listing_product_id'])
            ->where('listing_product_id IN (?)', $productsIds)
            ->where('is_variation_parent = ?', 1);

        $productsIds = $this->resourceConnection->getConnection()->fetchCol($select);

        foreach ($productsIds as $productId) {
            $listingProduct = $this->amazonFactory->getObjectLoaded('Listing\Product', $productId);
            $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    //########################################
}
