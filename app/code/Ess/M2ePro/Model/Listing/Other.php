<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Listing;

/**
 * Class Other
 * @package Ess\M2ePro\Model\Listing
 */
class Other extends \Ess\M2ePro\Model\ActiveRecord\Component\Parent\AbstractModel
{
    /**
     * @var \Ess\M2ePro\Model\Account
     */
    private $accountModel = null;

    /**
     * @var \Ess\M2ePro\Model\Marketplace
     */
    private $marketplaceModel = null;

    /**
     * @var \Ess\M2ePro\Model\Magento\Product\Cache
     */
    protected $magentoProductModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('Ess\M2ePro\Model\ResourceModel\Listing\Other');
    }

    //########################################

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function isLocked()
    {
        if ($this->isComponentModeEbay() && $this->getAccount()->getChildObject()->isModeSandbox()) {
            return false;
        }

        return parent::isLocked();
    }

    public function delete()
    {
        $temp = parent::delete();
        $temp && $this->accountModel = null;
        $temp && $this->marketplaceModel = null;
        $temp && $this->magentoProductModel = null;
        return $temp;
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Account
     */
    public function getAccount()
    {
        if ($this->accountModel === null) {
            $this->accountModel = $this->parentFactory->getCachedObjectLoaded(
                $this->getComponentMode(),
                'Account',
                $this->getData('account_id')
            );
        }

        return $this->accountModel;
    }

    /**
     * @param \Ess\M2ePro\Model\Account $instance
     */
    public function setAccount(\Ess\M2ePro\Model\Account $instance)
    {
         $this->accountModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return \Ess\M2ePro\Model\Marketplace
     */
    public function getMarketplace()
    {
        if ($this->marketplaceModel === null) {
            $this->marketplaceModel = $this->parentFactory->getCachedObjectLoaded(
                $this->getComponentMode(),
                'Marketplace',
                $this->getData('marketplace_id')
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param \Ess\M2ePro\Model\Marketplace $instance
     */
    public function setMarketplace(\Ess\M2ePro\Model\Marketplace $instance)
    {
         $this->marketplaceModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return \Ess\M2ePro\Model\Magento\Product\Cache
     * @throws \Ess\M2ePro\Model\Exception
     */
    public function getMagentoProduct()
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        if ($this->getProductId() === null) {
            throw new \Ess\M2ePro\Model\Exception('Product id is not set');
        }

        return $this->magentoProductModel = $this->modelFactory->getObject('Magento_Product_Cache')
            ->setStoreId($this->getChildObject()->getRelatedStoreId())
            ->setProductId($this->getProductId());
    }

    /**
     * @param \Ess\M2ePro\Model\Magento\Product\Cache $instance
     */
    public function setMagentoProduct(\Ess\M2ePro\Model\Magento\Product\Cache $instance)
    {
        $this->magentoProductModel = $instance;
    }

    //########################################

    /**
     * @return int
     */
    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        $temp = $this->getData('product_id');
        return $temp === null ? null : (int)$temp;
    }

    public function getAdditionalData()
    {
        return $this->getSettings('additional_data');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    /**
     * @return int
     */
    public function getStatusChanger()
    {
        return (int)$this->getData('status_changer');
    }

    //########################################

    /**
     * @return bool
     */
    public function isNotListed()
    {
        return $this->getStatus() == \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED;
    }

    /**
     * @return bool
     */
    public function isUnknown()
    {
        return $this->getStatus() == \Ess\M2ePro\Model\Listing\Product::STATUS_UNKNOWN;
    }

    /**
     * @return bool
     */
    public function isBlocked()
    {
        return $this->getStatus() == \Ess\M2ePro\Model\Listing\Product::STATUS_BLOCKED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isListed()
    {
        return $this->getStatus() == \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->getStatus() == \Ess\M2ePro\Model\Listing\Product::STATUS_HIDDEN;
    }

    /**
     * @return bool
     */
    public function isSold()
    {
        return $this->getStatus() == \Ess\M2ePro\Model\Listing\Product::STATUS_SOLD;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->getStatus() == \Ess\M2ePro\Model\Listing\Product::STATUS_STOPPED;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->getStatus() == \Ess\M2ePro\Model\Listing\Product::STATUS_FINISHED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isListable()
    {
        return ($this->isNotListed() || $this->isSold() ||
                $this->isStopped() || $this->isFinished() ||
                $this->isHidden() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    /**
     * @return bool
     */
    public function isRelistable()
    {
        return ($this->isSold() || $this->isStopped() ||
                $this->isFinished() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    /**
     * @return bool
     */
    public function isRevisable()
    {
        return ($this->isListed() || $this->isHidden() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    /**
     * @return bool
     */
    public function isStoppable()
    {
        return ($this->isListed() || $this->isHidden() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    //########################################

    public function reviseAction(array $params = [])
    {
        return $this->getChildObject()->reviseAction($params);
    }

    public function relistAction(array $params = [])
    {
        return $this->getChildObject()->relistAction($params);
    }

    public function stopAction(array $params = [])
    {
        return $this->getChildObject()->stopAction($params);
    }

    //########################################

    public function unmapDeletedProduct($product)
    {
        $productId = $product instanceof \Magento\Catalog\Model\Product ?
                        (int)$product->getId() : (int)$product;

        $listingsOther = $this->activeRecordFactory->getObject('Listing\Other')
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->getItems();

        foreach ($listingsOther as $listingOther) {
            $listingOther->unmapProduct(\Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION);
        }
    }

    // ---------------------------------------

    /**
     * @param int $productId
     * @param int $logsInitiator
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function mapProduct($productId, $logsInitiator = \Ess\M2ePro\Helper\Data::INITIATOR_UNKNOWN)
    {
        $this->addData(['product_id'=>$productId])->save();
        $this->getChildObject()->afterMapProduct();

        $logModel = $this->activeRecordFactory->getObject('Listing_Other_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $logModel->addProductMessage(
            $this->getId(),
            $logsInitiator,
            null,
            \Ess\M2ePro\Model\Listing\Other\Log::ACTION_MAP_ITEM,
            // M2ePro\TRANSLATIONS
            // Item was successfully Mapped
            'Item was successfully Mapped',
            \Ess\M2ePro\Model\Log\AbstractModel::TYPE_NOTICE,
            \Ess\M2ePro\Model\Log\AbstractModel::PRIORITY_MEDIUM
        );
    }

    /**
     * @param int $logsInitiator
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function unmapProduct($logsInitiator = \Ess\M2ePro\Helper\Data::INITIATOR_UNKNOWN)
    {
        $this->getChildObject()->beforeUnmapProduct();
        $this->setData('product_id', null)->save();

        $logModel = $this->activeRecordFactory->getObject('Listing_Other_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $logModel->addProductMessage(
            $this->getId(),
            $logsInitiator,
            null,
            \Ess\M2ePro\Model\Listing\Other\Log::ACTION_UNMAP_ITEM,
            // M2ePro\TRANSLATIONS
            // Item was successfully Unmapped
            'Item was successfully Unmapped',
            \Ess\M2ePro\Model\Log\AbstractModel::TYPE_NOTICE,
            \Ess\M2ePro\Model\Log\AbstractModel::PRIORITY_MEDIUM
        );
    }

    //########################################
}
