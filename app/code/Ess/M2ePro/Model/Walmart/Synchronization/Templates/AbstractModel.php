<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Walmart\Synchronization\Templates;

/**
 * Class AbstractModel
 * @package Ess\M2ePro\Model\Walmart\Synchronization\Templates
 */
abstract class AbstractModel extends \Ess\M2ePro\Model\Walmart\Synchronization\AbstractModel
{
    protected $productChangesManager = null;

    //########################################

    /**
     * @param \Ess\M2ePro\Model\Synchronization\Templates\ProductChanges\Manager $object
     */
    public function setProductChangesManager(\Ess\M2ePro\Model\Synchronization\Templates\ProductChanges\Manager $object)
    {
        $this->productChangesManager = $object;
    }

    /**
     * @return \Ess\M2ePro\Model\Synchronization\Templates\ProductChanges\Manager
     */
    public function getProductChangesManager()
    {
        return $this->productChangesManager;
    }

    //########################################

    protected function getType()
    {
        return \Ess\M2ePro\Model\Synchronization\Task\AbstractComponent::TEMPLATES;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Templates\\'.$taskPath);
    }

    //########################################

    protected function logError(
        \Ess\M2ePro\Model\Listing\Product $listingProduct,
        \Exception $exception,
        $sendToServer = true
    ) {
        /** @var \Ess\M2ePro\Model\Walmart\Listing\Log $logModel */
        $logModel = $this->activeRecordFactory->getObject('Walmart_Listing_Log');

        $logModel->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            \Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION,
            $logModel->getResource()->getNextActionId(),
            $this->getActionForLog(),
            $exception->getMessage(),
            \Ess\M2ePro\Model\Log\AbstractModel::TYPE_ERROR,
            \Ess\M2ePro\Model\Log\AbstractModel::PRIORITY_HIGH
        );

        $this->getHelper('Module\Exception')->process($exception, $sendToServer);
    }

    //########################################
}
