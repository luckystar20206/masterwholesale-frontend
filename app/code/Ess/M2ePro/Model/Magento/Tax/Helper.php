<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Magento\Tax;

use Ess\M2ePro\Model\Magento\Tax\Rule\Builder;

/**
 * Class Helper
 * @package Ess\M2ePro\Model\Magento\Tax
 */
class Helper extends \Ess\M2ePro\Model\AbstractModel
{
    protected $calculationRateFactory;
    protected $taxConfig;
    protected $taxCalculation;
    protected $storeManager;

    //########################################

    public function __construct(
        \Magento\Tax\Model\Calculation\RateFactory $calculationRateFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        $this->calculationRateFactory = $calculationRateFactory;
        $this->taxConfig = $taxConfig;
        $this->taxCalculation = $taxCalculation;
        $this->storeManager = $storeManager;
        parent::__construct($helperFactory, $modelFactory);
    }

    //########################################

    public function hasRatesForCountry($countryId)
    {
        return $this->calculationRateFactory->create()
            ->getCollection()
            ->addFieldToFilter('tax_country_id', $countryId)
            ->addFieldToFilter('code', ['neq' => Builder::TAX_RATE_CODE_PRODUCT])
            ->addFieldToFilter('code', ['neq' => Builder::TAX_RATE_CODE_SHIPPING])
            ->addFieldToFilter('code', ['neq' => 'eBay Tax Rate']) // backward compatibility with m2e 3.x.x
            ->getSize();
    }

    /**
     * Return store tax rate for shipping
     *
     * @param \Magento\Store\Model\Store $store
     * @return float
     */
    public function getStoreShippingTaxRate($store)
    {
        $request = new \Magento\Framework\DataObject();
        $request->setProductClassId($this->taxConfig->getShippingTaxClass($store));

        return $this->taxCalculation->getStoreRate($request, $store);
    }

    public function isCalculationBasedOnOrigin($store)
    {
        return $this->storeManager
                    ->getStore($store)
                    ->getConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_BASED_ON) == 'origin';
    }

    //########################################
}
