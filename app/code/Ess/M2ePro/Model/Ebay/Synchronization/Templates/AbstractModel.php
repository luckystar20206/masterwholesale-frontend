<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Synchronization\Templates;

use Ess\M2ePro\Model\Synchronization\Templates\ProductChanges\Manager;

/**
 * Class AbstractModel
 * @package Ess\M2ePro\Model\Ebay\Synchronization\Templates
 */
abstract class AbstractModel extends \Ess\M2ePro\Model\Ebay\Synchronization\AbstractModel
{
    /**
     * @var Manager
     */
    protected $productChangesManager = null;

    protected $resourceConnection;

    //########################################

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($ebayFactory, $activeRecordFactory, $helperFactory, $modelFactory);
    }

    //########################################

    public function setProductChangesManager(Manager $manager)
    {
        $this->productChangesManager = $manager;
        return $this;
    }

    /**
     * @return Manager
     */
    public function getProductChangesManager()
    {
        return $this->productChangesManager;
    }

    //########################################

    /**
     * @return string
     */
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
        /** @var \Ess\M2ePro\Model\Ebay\Listing\Log $logModel */
        $logModel = $this->activeRecordFactory->getObject('Ebay_Listing_Log');

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
