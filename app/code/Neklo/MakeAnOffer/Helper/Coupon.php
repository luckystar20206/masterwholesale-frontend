<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Helper;

use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Product\Subselect;

class Coupon extends AbstractHelper
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon\MassgeneratorFactory
     */
    private $massgeneratorFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    private $customerGroup;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * Coupon constructor.
     * @param Context $context
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\Coupon\MassgeneratorFactory $massgeneratorFactory
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Config $configHelper
     */
    public function __construct(
        Context $context,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\Coupon\MassgeneratorFactory $massgeneratorFactory,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Neklo\MakeAnOffer\Helper\Config $configHelper
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->massgeneratorFactory = $massgeneratorFactory;
        $this->customerGroup = $customerGroup;
        $this->date = $date;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * @param $sku
     * @param $qty
     * @param $discountSum
     * @param $storeId
     * @return mixed
     */
    public function generateCouponCode($id, $sku, $qty, $discountSum, $storeId)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $couponLifetime = $this->configHelper->getCouponLifetime($storeId);
        $fromDate = $this->date->gmtDate('Y-m-d');
        $toDate = $this->date->gmtDate('Y-m-d', "{$couponLifetime} days");
        $model = $this->ruleFactory->create();
        $model->setName("Make An Offer " . "#{$id}")
            ->setDescription('Auto Generation Coupon')
            ->setIsActive(true)
            ->setCustomerGroupIds($this->getCustomerGroups())
            ->setWebsiteIds([$websiteId])
            ->setFromDate($fromDate)
            ->setToDate($toDate)
            ->setSimpleAction(Rule::CART_FIXED_ACTION)
            ->setDiscountAmount($discountSum)
            ->setStopRulesProcessing(0)
            ->setUsesPerCoupon(1)
            ->setCouponType(Rule::COUPON_TYPE_SPECIFIC);
        $conditions = [];

        $conditions["1"] = [
            "type" => Combine::class,
            "aggregator" => "all",
            "value" => 1,
            "new_child" => "",
        ];
        $conditions["1--1"] = [
            'type' =>  Subselect::class,
            "attribute" => "qty",
            "operator" => ">=",
            "value" => $qty,
            'aggregator' => 'all',
        ];

        $conditions["1--1--1"] = [
            "type" => Product::class,
            "attribute" => "sku",
            "operator" => "==",
            "value" => $sku,
            'aggregator' => 'all',
        ];

        $model->setData('conditions', $conditions);

        $model->loadPost($model->getData());

        $massGenerator = $this->massgeneratorFactory->create();
        $length = $this->configHelper->getCouponLength($storeId);
        $prefix = $this->configHelper->getCouponPrefix($storeId);
        $suffix = $this->configHelper->getCouponSuffix($storeId);
        $dash = $this->configHelper->getCouponDash($storeId);
        $massGenerator->setLength($length);
        $massGenerator->setPrefix($prefix);
        $massGenerator->setSuffix($suffix);
        $massGenerator->setDash($dash);
        $generatedCode = $massGenerator->generateCode();
        $model->setCouponCode($generatedCode);
        $model->save();
        $model->acquireCoupon();

        return $generatedCode;
    }

    /**
     * Get customer groups
     *
     * @return array
     */
    private function getCustomerGroups()
    {
        $customerGroups = $this->customerGroup->toOptionArray();
        $customerGroupsIds = [];
        foreach ($customerGroups as $group) {
            $customerGroupsIds[] = $group['value'];
        }

        return $customerGroupsIds;
    }
}
