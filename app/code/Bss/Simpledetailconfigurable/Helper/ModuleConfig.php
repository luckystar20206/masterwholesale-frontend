<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Helper;

class ModuleConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;

    private $storeManager;

    private $storeId;

    private $localeFormat;

    private $jsonEncoder;

    private $currency;

    private $magentoVersion;

    private $videoHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $magentoVersion,
        \Magento\ProductVideo\Helper\Media $videoHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->localeFormat = $localeFormat;
        $this->jsonEncoder = $jsonEncoder;
        $this->currency = $currency;
        $this->magentoVersion = $magentoVersion;
        $this->videoHelper = $videoHelper;
    }

    public function serialize($data) {
        if (version_compare($this->getMagentoVersion(), '2.2.0', '<')) {
            return serialize($data);
        } else {
            $result = json_encode($data);
            if (false === $result) {
                throw new \InvalidArgumentException('Unable to serialize value.');
            }
            return $result;
        }
    }

    public function unserialize($string) {
        if (version_compare($this->getMagentoVersion(), '2.2.0', '<')) {
            return unserialize($string);
        } else {
            $result = json_decode($string, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Unable to unserialize value.');
            }
            return $result;
        }
    }

    public function getCurrencyRates()
    {
        $result = [];
        $currencies = $this->storeManager->getStore()->getAvailableCurrencyCodes(true);
        foreach ($currencies as $ck => $currency) {
            $result[$currency] = $this->storeManager->getStore()->getBaseCurrency()->getRate($currency);
        }
        return $result;
    }
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->storeId = $this->storeManager->getStore()->getId();
        }
        return $this->storeId;
    }

    public function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }

    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/Simpledetailconfigurable/Enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isShowSku()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/sku',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isShowName()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isShowDescription()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/desc',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
    
    public function isShowTierPrice()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/tier_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isShowStockStatus()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isShowImage()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/image',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getSuffix()
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/product_url_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
    
    public function isShowTax()
    {
        return $this->scopeConfig->getValue(
            'tax/display/type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isCatalogPriceIncludeTax()
    {
        return $this->scopeConfig->getValue(
            'tax/calculation/price_includes_tax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isCrossBorder()
    {
        return $this->scopeConfig->getValue(
            'tax/calculation/cross_border_trade_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getTaxCalculationBased()
    {
        return $this->scopeConfig->getValue(
            'tax/calculation/based_on',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function preselectConfig()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_advanced/preselect',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function minmaxConfig()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/min_max',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function incrementConfig()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/increment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isShowAdditionalInfo()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/additional_info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isChangeMetaData()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_details/meta_data',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function customUrl()
    {
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_advanced/url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    public function getFomatPrice()
    {
        $config = $this->localeFormat->getPriceFormat();
        return $this->jsonEncoder->encode($config);
    }

    public function getMagentoVersion()
    {
        return $this->magentoVersion->getVersion();
    }

    public function getVideoConfig()
    {
        $videoSettingData[] = [
            'playIfBase' => $this->videoHelper->getPlayIfBaseAttribute(),
            'showRelated' => $this->videoHelper->getShowRelatedAttribute(),
            'videoAutoRestart' => $this->videoHelper->getVideoAutoRestartAttribute(),
        ];
        return $videoSettingData;
    }

    public function getAllConfig()
    {
        $result = [];
        $result['enabled'] = $this->isModuleEnable();
        $result['baseUrl'] = $this->getBaseUrl();
        $result['CurrencySymbol'] = $this->getCurrencySymbol();
        $result['currency_rate'] = $this->getCurrencyRates();
        $result['fomatPrice'] = $this->getFomatPrice();
        $result['tax'] = $this->isShowTax();
        $result['tax_based_on'] = $this->getTaxCalculationBased();
        $result['catalog_price_include_tax'] = $this->isCatalogPriceIncludeTax();
        $result['cross_border'] = $this->isCrossBorder();
        $result['url_suffix'] = $this->getSuffix();
        $result['video'] = $this->getVideoConfig();
        if ($result['enabled']) {
            $result['sku'] = $this->isShowSku();
            $result['name'] = $this->isShowName();
            $result['desc'] = $this->isShowDescription();
            $result['stock'] = $this->isShowStockStatus();
            $result['tier_price'] = $this->isShowTierPrice();
            $result['images'] = $this->isShowImage();
            $result['url'] = $this->customUrl();
            $result['preselect'] = $this->preselectConfig();
            $result['min_max'] = $this->minmaxConfig();
            $result['increment'] = $this->incrementConfig();
            $result['additional_info'] = $this->isShowAdditionalInfo();
            $result['meta_data'] = $this->isChangeMetaData();
        } else {
            $result['sku'] = 0;
            $result['name'] = 0;
            $result['desc'] = 0;
            $result['stock'] = 0;
            $result['tier_price'] = 0;
            $result['images'] = 0;
            $result['url'] = 0;
            $result['preselect'] = 0;
            $result['min_max'] = 0;
            $result['increment'] = 0;
            $result['additional_info'] = 0;
            $result['meta_data'] = 0;
        }
        return $result;
    }
    public function getNullConfig()
    {
        $result = [];
        $result['enabled'] = 0;
        $result['baseUrl'] = $this->getBaseUrl();
        $result['CurrencySymbol'] = $this->getCurrencySymbol();
        $result['currency_rate'] = $this->getCurrencyRates();
        $result['fomatPrice'] = $this->getFomatPrice();
        $result['tax'] = $this->isShowTax();
        $result['tax_based_on'] = $this->getTaxCalculationBased();
        $result['catalog_price_include_tax'] = $this->isCatalogPriceIncludeTax();
        $result['cross_border'] = $this->isCrossBorder();
        $result['url_suffix'] = $this->getSuffix();
        $result['sku'] = 0;
        $result['name'] = 0;
        $result['desc'] = 0;
        $result['stock'] = 0;
        $result['tier_price'] = 0;
        $result['images'] = 0;
        $result['url'] = 0;
        $result['preselect'] = 0;
        $result['min_max'] = 0;
        $result['increment'] = 0;
        $result['additional_info'] = 0;
        $result['meta_data'] = 0;
        return $result;
    }
}
