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
namespace Bss\Simpledetailconfigurable\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Framework\Locale\Format;

/**
 * Swatch renderer block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableControl extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    const BSS_SWATCH_RENDERER_TEMPLATE = 'Bss_Simpledetailconfigurable::SimpledetailControl.phtml';

    private $linkData;

    private $moduleConfig;

    private $customerSession;

    private $localeFormat;

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        Format $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Bss\Simpledetailconfigurable\Helper\ProductData $linkData,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        array $data = []
    ) {
        $this->linkData = $linkData;
        $this->moduleConfig = $moduleConfig;
        $this->customerSession = $customerSession;
        $this->localeFormat = $localeFormat;
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data
        );
    }

    /**
     * Bss_commerce
     * Get child product data
     */
    public function getJsonChildProductData()
    {
        return $this->jsonEncoder->encode($this->linkData->getAllData($this->getProduct()->getEntityId()));
    }

    /**
     * Bss_commerce
     * Get module config
     */
    public function getJsonModuleConfig()
    {
        
        if ($this->linkData->getEnabledModuleOnProduct($this->getProduct()->getEntityId())['enabled'] != '0') {
            return $this->jsonEncoder->encode($this->moduleConfig->getAllConfig());
        }
    }

    public function getAllowProducts()
    {
        if (!$this->moduleConfig->isModuleEnable() || !$this->moduleConfig->isShowStockStatus()) {
            return parent::getAllowProducts();
        }
        if (!$this->hasAllowProducts()) {
            $products = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct(), null);
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    public function getJsonConfig()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();

        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');

        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->getAttributesDataCustom($currentProduct, $options);

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->getLocalFormatNumber($regularPrice->getAmount()->getValue()),
                ],
                'basePrice' => [
                    'amount' => $this->getLocalFormatNumber($finalPrice->getAmount()->getBaseAmount()),
                ],
                'finalPrice' => [
                    'amount' => $this->getLocalFormatNumber($finalPrice->getAmount()->getValue()),
                ],
            ],
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => $this->getOptionImages(),
            'index' => isset($options['index']) ? $options['index'] : []
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->jsonEncoder->encode($config);
    }

    private function getLocalFormatNumber($value)
    {
        if (version_compare($this->getMagentoVersion(), '2.2.0', '<')) {
            return $this->_registerJsPrice($value);
        } else {
            return $this->localeFormat->getNumber($value);
        }
    }

    public function getAttributesDataCustom($product, array $options = [])
    {
        $defaultValues = [];
        $attributes = [];
        foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
            $attributeOptionsData = $this->getAttributeOptionsDataCustom($attribute, $options);
            if ($attributeOptionsData) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeId = $productAttribute->getId();
                $attributes[$attributeId] = [
                    'id' => $attributeId,
                    'code' => $productAttribute->getAttributeCode(),
                    'label' => $productAttribute->getStoreLabel($product->getStoreId()),
                    'options' => $attributeOptionsData,
                    'position' => $attribute->getPosition(),
                ];
                $defaultValues[$attributeId] = $this->getAttributeConfigValueCustom($attributeId, $product);
            }
        }
        return [
            'attributes' => $attributes,
            'defaultValues' => $defaultValues,
        ];
    }

    /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getAttributeOptionsDataCustom($attribute, $config)
    {
        $attributeOptionsData = [];
        $options = $this->getProduct()->getAttributes()[$attribute->getProductAttribute()->getAttributeCode()]->getOptions();
        foreach ($options as $attributeOption) {
            $optionId = $attributeOption->getValue();
            if (isset($config[$attribute->getAttributeId()][$optionId])) {
                $attributeOptionsData[] = [
                    'id' => $optionId,
                    'label' => $attributeOption->getLabel(),
                    'products' => isset($config[$attribute->getAttributeId()][$optionId])
                        ? $config[$attribute->getAttributeId()][$optionId]
                        : [],
                ];
            }
        }
        return $attributeOptionsData;
    }

    /**
     * @param int $attributeId
     * @param Product $product
     * @return mixed|null
     */
    protected function getAttributeConfigValueCustom($attributeId, $product)
    {
        return $product->hasPreconfiguredValues()
            ? $product->getPreconfiguredValues()->getData('super_attribute/' . $attributeId)
            : null;
    }

    public function getMagentoVersion()
    {
        return $this->moduleConfig->getMagentoVersion();
    }
    
    /**
     * Get Key for caching block content
     *
     * @return string
     */
    public function getRendererTemplate()
    {
        if ($this->moduleConfig->isModuleEnable()
            && $this->linkData->getEnabledModuleOnProduct($this->getProduct()->getEntityId())['enabled']) {
            return self::BSS_SWATCH_RENDERER_TEMPLATE;
        }
        return $this->isProductHasSwatchAttribute() ?
            self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }

    public function getCacheLifetime()
    {
        return null;
    }
}
