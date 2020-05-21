<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Template;

use Ess\M2ePro\Controller\Adminhtml\Context;

/**
 * Class Shipping
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Template
 */
abstract class Shipping extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Template
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

    protected function setShippingTemplateForProducts($productsIds, $templateId, $shippingMode)
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
                /** @var \Ess\M2ePro\Model\Listing\Product $listingProduct */

                $oldSnapshots[$listingProduct->getId()] = array_merge(
                    $listingProduct->getDataSnapshot(),
                    $listingProduct->getChildObject()->getDataSnapshot()
                );

                $field = $shippingMode == \Ess\M2ePro\Model\Amazon\Account::SHIPPING_MODE_OVERRIDE
                    ? 'template_shipping_override_id'
                    : 'template_shipping_template_id';

                $listingProduct->getChildObject()->setData($field, $templateId);
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
            /** @var \Ess\M2ePro\Model\Listing\Product $listingProduct */

            $listingProduct->getChildObject()->setSynchStatusNeed(
                array_merge(
                    $listingProduct->getDataSnapshot(),
                    $listingProduct->getChildObject()->getDataSnapshot()
                ),
                $oldSnapshots[$listingProduct->getId()]
            );
        }
    }

    //########################################
}
