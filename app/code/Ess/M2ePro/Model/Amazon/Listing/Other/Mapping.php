<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
namespace Ess\M2ePro\Model\Amazon\Listing\Other;

/**
 * Class Mapping
 * @package Ess\M2ePro\Model\Amazon\Listing\Other
 */
class Mapping extends \Ess\M2ePro\Model\AbstractModel
{
    protected $productFactory;

    /**
     * @var \Ess\M2ePro\Model\Account|null
     */
    protected $account = null;

    protected $mappingSettings = null;

    protected $amazonFactory;

    //########################################

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        $this->productFactory = $productFactory;
        $this->amazonFactory = $amazonFactory;
        parent::__construct($helperFactory, $modelFactory);
    }

    //########################################

    public function initialize(\Ess\M2ePro\Model\Account $account = null)
    {
        $this->account = $account;
        $this->mappingSettings = null;
    }

    //########################################

    /**
     * @param array $otherListings
     * @return bool
     */
    public function autoMapOtherListingsProducts(array $otherListings)
    {
        $otherListingsFiltered = [];

        foreach ($otherListings as $otherListing) {
            if (!($otherListing instanceof \Ess\M2ePro\Model\Listing\Other)) {
                continue;
            }

            /** @var $otherListing \Ess\M2ePro\Model\Listing\Other */

            if ($otherListing->getProductId()) {
                continue;
            }

            $otherListingsFiltered[] = $otherListing;
        }

        if (count($otherListingsFiltered) <= 0) {
            return false;
        }

        $sortedItems = [];

        /** @var $otherListing \Ess\M2ePro\Model\Listing\Other */
        foreach ($otherListingsFiltered as $otherListing) {
            $sortedItems[$otherListing->getAccountId()][] = $otherListing;
        }

        $result = true;

        foreach ($sortedItems as $otherListings) {
            foreach ($otherListings as $otherListing) {
                /** @var $otherListing \Ess\M2ePro\Model\Listing\Other */
                $temp = $this->autoMapOtherListingProduct($otherListing);
                $temp === false && $result = false;
            }
        }

        return $result;
    }

    /**
     * @param \Ess\M2ePro\Model\Listing\Other $otherListing
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function autoMapOtherListingProduct(\Ess\M2ePro\Model\Listing\Other $otherListing)
    {
        if ($otherListing->getProductId()) {
            return false;
        }

        $this->setAccountByOtherListingProduct($otherListing);

        if (!$this->getAccount()->getChildObject()->isOtherListingsMappingEnabled()) {
            return false;
        }

        $mappingSettings = $this->getMappingRulesByPriority();

        foreach ($mappingSettings as $type) {
            $magentoProductId = null;

            if ($type == 'general_id') {
                $magentoProductId = $this->getGeneralIdMappedMagentoProductId($otherListing);
            }

            if ($type == 'sku') {
                $magentoProductId = $this->getSkuMappedMagentoProductId($otherListing);
            }

            if ($type == 'title') {
                $magentoProductId = $this->getTitleMappedMagentoProductId($otherListing);
            }

            if ($magentoProductId === null) {
                continue;
            }

            $otherListing->mapProduct($magentoProductId, \Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION);

            return true;
        }

        return false;
    }

    //########################################

    protected function getMappingRulesByPriority()
    {
        if ($this->mappingSettings !== null) {
            return $this->mappingSettings;
        }

        $this->mappingSettings = [];

        foreach ($this->getAccount()->getChildObject()->getOtherListingsMappingSettings() as $key => $value) {
            if ((int)$value['mode'] == 0) {
                continue;
            }
            for ($i=0; $i<10; $i++) {
                if (!isset($this->mappingSettings[(int)$value['priority']+$i])) {
                    $this->mappingSettings[(int)$value['priority']+$i] = (string)$key;
                    break;
                }
            }
        }

        ksort($this->mappingSettings);

        return $this->mappingSettings;
    }

    // ---------------------------------------

    protected function getGeneralIdMappedMagentoProductId(\Ess\M2ePro\Model\Listing\Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getGeneralId();

        if (empty($temp)) {
            return null;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingGeneralIdModeCustomAttribute()) {
            $storeId = $otherListing->getChildObject()->getRelatedStoreId();
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingGeneralIdAttribute();
            $attributeValue = trim($otherListing->getChildObject()->getGeneralId());

            $productObj = $this->productFactory->create()->setStoreId($storeId);
            $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

            if ($productObj && $productObj->getId() &&
                $this->isMagentoProductTypeAllowed($productObj->getTypeId())) {
                return $productObj->getId();
            }
        }

        return null;
    }

    /**
     * @param \Ess\M2ePro\Model\Listing\Other $otherListing
     * @return null|int
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function getSkuMappedMagentoProductId(\Ess\M2ePro\Model\Listing\Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getSku();

        if (empty($temp)) {
            return null;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeProductId()) {
            $productId = trim($otherListing->getChildObject()->getSku());

            if (!ctype_digit($productId) || (int)$productId <= 0) {
                return null;
            }

            $product = $this->productFactory->create()->load($productId);

            if ($product->getId() && $this->isMagentoProductTypeAllowed($product->getTypeId())) {
                return $product->getId();
            }

            return null;
        }

        $attributeCode = null;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeDefault()) {
            $attributeCode = 'sku';
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingSkuAttribute();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getChildObject()->getRelatedStoreId();
        $attributeValue = trim($otherListing->getChildObject()->getSku());

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId() &&
            $this->isMagentoProductTypeAllowed($productObj->getTypeId())) {
            return $productObj->getId();
        }

        return null;
    }

    /**
     * @param \Ess\M2ePro\Model\Listing\Other $otherListing
     * @return null|int
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function getTitleMappedMagentoProductId(\Ess\M2ePro\Model\Listing\Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getTitle();

        if (empty($temp)) {
            return null;
        }

        $attributeCode = null;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingTitleModeDefault()) {
            $attributeCode = 'name';
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingTitleModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingTitleAttribute();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getChildObject()->getRelatedStoreId();
        $attributeValue = trim($otherListing->getChildObject()->getTitle());

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId() &&
            $this->isMagentoProductTypeAllowed($productObj->getTypeId())) {
            return $productObj->getId();
        }

        $findCount = preg_match('/^.+(\[(.+)\])$/', $attributeValue, $tempMatches);
        if ($findCount > 0 && isset($tempMatches[1])) {
            $attributeValue = trim(str_replace($tempMatches[1], '', $attributeValue));
            $productObj = $this->productFactory->create()->setStoreId($storeId);
            $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

            if ($productObj && $productObj->getId() &&
                $this->isMagentoProductTypeAllowed($productObj->getTypeId())) {
                return $productObj->getId();
            }
        }

        return null;
    }

    //########################################

    protected function isMagentoProductTypeAllowed($type)
    {
        $knownTypes = $this->getHelper('Magento\Product')->getOriginKnownTypes();
        return in_array($type, $knownTypes);
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Listing\Other $otherListing
     */
    protected function setAccountByOtherListingProduct(\Ess\M2ePro\Model\Listing\Other $otherListing)
    {
        if ($this->account !== null && $this->account->getId() == $otherListing->getAccountId()) {
            return;
        }

        $this->account = $this->amazonFactory->getCachedObjectLoaded(
            'Account',
            $otherListing->getAccountId()
        );

        $this->mappingSettings = null;
    }

    //########################################
}
