<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Template\Description\Definition;

/**
 * Class Source
 * @package Ess\M2ePro\Model\Amazon\Template\Description\Definition
 */
class Source extends \Ess\M2ePro\Model\AbstractModel
{
    const GALLERY_IMAGES_COUNT_MAX = 8;

    const WEIGHT_TYPE_ITEM_DIMENSIONS    = 'item_dimensions';
    const WEIGHT_TYPE_PACKAGE_DIMENSIONS = 'package_dimensions';
    const WEIGHT_TYPE_SHIPPING           = 'shipping';
    const WEIGHT_TYPE_PACKAGE            = 'package';

    /**
     * @var $magentoProduct \Ess\M2ePro\Model\Magento\Product
     */
    private $magentoProduct = null;

    /**
     * @var $descriptionDefinitionTemplateModel \Ess\M2ePro\Model\Amazon\Template\Description\Definition
     */
    private $descriptionDefinitionTemplateModel = null;

    protected $emailTemplateFilter;

    //########################################

    public function __construct(
        \Magento\Email\Model\Template\Filter $emailTemplateFilter,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        $this->emailTemplateFilter = $emailTemplateFilter;
        parent::__construct($helperFactory, $modelFactory);
    }

    //########################################

    /**
     * @param \Ess\M2ePro\Model\Magento\Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(\Ess\M2ePro\Model\Magento\Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return \Ess\M2ePro\Model\Magento\Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Amazon\Template\Description\Definition $instance
     * @return $this
     */
    public function setDescriptionDefinitionTemplate(\Ess\M2ePro\Model\Amazon\Template\Description\Definition $instance)
    {
        $this->descriptionDefinitionTemplateModel = $instance;
        return $this;
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Template\Description\Definition
     */
    public function getDescriptionDefinitionTemplate()
    {
        return $this->descriptionDefinitionTemplateModel;
    }

    //########################################

    /**
     * @return string
     */
    public function getTitle()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getTitleSource();

        switch ($src['mode']) {
            case \Ess\M2ePro\Model\Amazon\Template\Description\Definition::TITLE_MODE_PRODUCT:
                $title = $this->getMagentoProduct()->getName();
                break;

            case \Ess\M2ePro\Model\Amazon\Template\Description\Definition::TITLE_MODE_CUSTOM:
                $title = $this->getHelper('Module_Renderer_Description')->parseTemplate(
                    $src['template'],
                    $this->getMagentoProduct()
                );
                break;

            default:
                $title = $this->getMagentoProduct()->getName();
                break;
        }

        return $title;
    }

    /**
     * @return null|string
     */
    public function getBrand()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getBrandSource();

        if ($this->getDescriptionDefinitionTemplate()->isBrandModeNone()) {
            return null;
        }

        if ($this->getDescriptionDefinitionTemplate()->isBrandModeCustomValue()) {
            return trim($src['custom_value']);
        }

        return trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
    }

    /**
     * @return int|null|string
     */
    public function getItemPackageQuantity()
    {
        $result = '';
        $src = $this->getDescriptionDefinitionTemplate()->getItemPackageQuantitySource();

        if ($this->getDescriptionDefinitionTemplate()->isItemPackageQuantityModeNone()) {
            $result = null;
        }

        if ($this->getDescriptionDefinitionTemplate()->isItemPackageQuantityModeCustomValue()) {
            $result = (int)$src['value'];
        }

        if ($this->getDescriptionDefinitionTemplate()->isItemPackageQuantityModeCustomAttribute()) {
            $result = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    /**
     * @return int|null|string
     */
    public function getNumberOfItems()
    {
        $result = '';
        $src = $this->getDescriptionDefinitionTemplate()->getNumberOfItemsSource();

        if ($this->getDescriptionDefinitionTemplate()->isNumberOfItemsModeNone()) {
            $result = null;
        }

        if ($this->getDescriptionDefinitionTemplate()->isNumberOfItemsModeCustomValue()) {
            $result = (int)$src['value'];
        }

        if ($this->getDescriptionDefinitionTemplate()->isNumberOfItemsModeCustomAttribute()) {
            $result = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    /**
     * @return float|null
     */
    public function getMsrpRrp()
    {
        $result = '';

        if ($this->getDescriptionDefinitionTemplate()->isMsrpRrpModeNone()) {
            return null;
        }

        if ($this->getDescriptionDefinitionTemplate()->isMsrpRrpModeCustomAttribute()) {
            $src = $this->getDescriptionDefinitionTemplate()->getMsrpRrpSource();
            $result = $this->getMagentoProductAttributeValue(
                $src['custom_attribute'],
                $this->getMagentoProduct()->getStoreId()
            );
        }

        is_string($result) && $result = str_replace(',', '.', $result);

        return round((float)$result, 2);
    }

    /**
     * @return mixed|string
     * @throws \Ess\M2ePro\Model\Exception
     */
    public function getDescription()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getDescriptionSource();

        switch ($src['mode']) {
            case \Ess\M2ePro\Model\Amazon\Template\Description\Definition::DESCRIPTION_MODE_PRODUCT:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $this->emailTemplateFilter->filter($description);
                break;

            case \Ess\M2ePro\Model\Amazon\Template\Description\Definition::DESCRIPTION_MODE_SHORT:
                $description = $this->getMagentoProduct()->getProduct()->getShortDescription();
                $description = $this->emailTemplateFilter->filter($description);
                break;

            case \Ess\M2ePro\Model\Amazon\Template\Description\Definition::DESCRIPTION_MODE_CUSTOM:
                $description = $this->getHelper('Module_Renderer_Description')->parseTemplate(
                    $src['template'],
                    $this->getMagentoProduct()
                );
                break;

            default:
                $description = '';
                break;
        }

        $allowedTags = ['<p>', '<br>', '<ul>', '<li>', '<b>'];

        $description = str_replace(['<![CDATA[', ']]>'], '', $description);
        $description = strip_tags($description, implode($allowedTags));

        return $description;
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getTargetAudience()
    {
        if ($this->getDescriptionDefinitionTemplate()->isTargetAudienceModeNone()) {
            return [];
        }

        $audience = [];
        $src = $this->getDescriptionDefinitionTemplate()->getTargetAudienceSource();

        foreach ($src['template'] as $bullet) {
            $audience[] = strip_tags(
                $this->getHelper('Module_Renderer_Description')->parseTemplate(
                    $bullet,
                    $this->getMagentoProduct()
                )
            );
        }

        return $audience;
    }

    /**
     * @return array
     */
    public function getBulletPoints()
    {
        if ($this->getDescriptionDefinitionTemplate()->isBulletPointsModeNone()) {
            return [];
        }

        $bullets = [];
        $src = $this->getDescriptionDefinitionTemplate()->getBulletPointsSource();

        foreach ($src['template'] as $bullet) {
            $bullets[] = strip_tags(
                $this->getHelper('Module_Renderer_Description')->parseTemplate(
                    $bullet,
                    $this->getMagentoProduct()
                )
            );
        }

        return $bullets;
    }

    /**
     * @return array
     */
    public function getSearchTerms()
    {
        if ($this->getDescriptionDefinitionTemplate()->isSearchTermsModeNone()) {
            return [];
        }

        $searchTerms = [];
        $src = $this->getDescriptionDefinitionTemplate()->getSearchTermsSource();

        foreach ($src['template'] as $searchTerm) {
            $searchTerms[] = $this->getHelper('Module_Renderer_Description')->parseTemplate(
                $searchTerm,
                $this->getMagentoProduct()
            );
        }

        return $searchTerms;
    }

    // ---------------------------------------

    /**
     * @return null|string
     */
    public function getManufacturer()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getManufacturerSource();

        if ($this->getDescriptionDefinitionTemplate()->isManufacturerModeNone()) {
            return null;
        }

        if ($this->getDescriptionDefinitionTemplate()->isManufacturerModeCustomValue()) {
            return trim($src['custom_value']);
        }

        return trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
    }

    /**
     * @return null|string
     */
    public function getManufacturerPartNumber()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getManufacturerPartNumberSource();

        if ($this->getDescriptionDefinitionTemplate()->isManufacturerPartNumberModeNone()) {
            return null;
        }

        if ($this->getDescriptionDefinitionTemplate()->isManufacturerPartNumberModeCustomValue()) {
            return trim($src['custom_value']);
        }

        return trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getItemDimensionsVolume()
    {
        $volume = [];

        if ($this->getDescriptionDefinitionTemplate()->isItemDimensionsVolumeModeNone()) {
            return $volume;
        }

        $src = $this->getDescriptionDefinitionTemplate()->getItemDimensionsVolumeSource();

        if ($this->getDescriptionDefinitionTemplate()->isItemDimensionsVolumeModeCustomValue()) {
            $volume = [
                'length' => $src['length_custom_value'],
                'width'  => $src['width_custom_value'],
                'height' => $src['height_custom_value']
            ];
        } else {
            $volume = [
                'length' => trim($this->getMagentoProduct()->getAttributeValue($src['length_custom_attribute'])),
                'width'  => trim($this->getMagentoProduct()->getAttributeValue($src['width_custom_attribute'])),
                'height' => trim($this->getMagentoProduct()->getAttributeValue($src['height_custom_attribute']))
            ];
        }

        return $volume;
    }

    /**
     * @return null|string
     */
    public function getItemDimensionsVolumeUnitOfMeasure()
    {
        $unitOfMeasure = null;

        if ($this->getDescriptionDefinitionTemplate()->isItemDimensionsVolumeModeNone()) {
            return $unitOfMeasure;
        }

        $src = $this->getDescriptionDefinitionTemplate()->getItemDimensionsVolumeUnitOfMeasureSource();

        $unitOfMeasure = $src['custom_value'];
        if ($this->getDescriptionDefinitionTemplate()->isItemDimensionsVolumeUnitOfMeasureModeCustomAttribute()) {
            $unitOfMeasure = trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
        }

        return $unitOfMeasure;
    }

    /**
     * @return float|null|string
     */
    public function getItemDimensionsWeight()
    {
        return $this->getWeight(self::WEIGHT_TYPE_ITEM_DIMENSIONS);
    }

    /**
     * @return null|string
     */
    public function getItemDimensionsWeightUnitOfMeasure()
    {
        return $this->getWeightUnitOfMeasure(self::WEIGHT_TYPE_ITEM_DIMENSIONS);
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getPackageDimensionsVolume()
    {
        $volume = [];

        if ($this->getDescriptionDefinitionTemplate()->isPackageDimensionsVolumeModeNone()) {
            return $volume;
        }

        $src = $this->getDescriptionDefinitionTemplate()->getPackageDimensionsVolumeSource();

        if ($this->getDescriptionDefinitionTemplate()->isPackageDimensionsVolumeModeCustomValue()) {
            $volume = [
                'length' => $src['length_custom_value'],
                'width'  => $src['width_custom_value'],
                'height' => $src['height_custom_value']
            ];
        } else {
            $volume = [
                'length' => trim($this->getMagentoProduct()->getAttributeValue($src['length_custom_attribute'])),
                'width'  => trim($this->getMagentoProduct()->getAttributeValue($src['width_custom_attribute'])),
                'height' => trim($this->getMagentoProduct()->getAttributeValue($src['height_custom_attribute']))
            ];
        }

        return $volume;
    }

    /**
     * @return null|string
     */
    public function getPackageDimensionsVolumeUnitOfMeasure()
    {
        $unitOfMeasure = null;

        if ($this->getDescriptionDefinitionTemplate()->isPackageDimensionsVolumeModeNone()) {
            return $unitOfMeasure;
        }

        $src = $this->getDescriptionDefinitionTemplate()->getPackageDimensionsVolumeUnitOfMeasureSource();

        $unitOfMeasure = $src['custom_value'];
        if ($this->getDescriptionDefinitionTemplate()->isPackageDimensionsVolumeUnitOfMeasureModeCustomAttribute()) {
            $unitOfMeasure = trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
        }

        return $unitOfMeasure;
    }

    // ---------------------------------------

    /**
     * @return float|null|string
     */
    public function getPackageWeight()
    {
        return $this->getWeight(self::WEIGHT_TYPE_PACKAGE);
    }

    /**
     * @return null|string
     */
    public function getPackageWeightUnitOfMeasure()
    {
        return $this->getWeightUnitOfMeasure(self::WEIGHT_TYPE_PACKAGE);
    }

    // ---------------------------------------

    /**
     * @return float|null|string
     */
    public function getShippingWeight()
    {
        return $this->getWeight(self::WEIGHT_TYPE_SHIPPING);
    }

    /**
     * @return null|string
     */
    public function getShippingWeightUnitOfMeasure()
    {
        return $this->getWeightUnitOfMeasure(self::WEIGHT_TYPE_SHIPPING);
    }

    // ---------------------------------------

    /**
     * @param $weightType
     * @return float|null|string
     */
    private function getWeight($weightType)
    {
        $src = null;

        switch ($weightType) {
            case self::WEIGHT_TYPE_ITEM_DIMENSIONS:
                $src = $this->getDescriptionDefinitionTemplate()->getItemDimensionsWeightSource();
                break;

            case self::WEIGHT_TYPE_PACKAGE:
                $src = $this->getDescriptionDefinitionTemplate()->getPackageWeightSource();
                break;

            case self::WEIGHT_TYPE_SHIPPING:
                $src = $this->getDescriptionDefinitionTemplate()->getShippingWeightSource();
                break;
        }

        if (!$src || $src['mode'] == \Ess\M2ePro\Model\Amazon\Template\Description\Definition::WEIGHT_MODE_NONE) {
            return null;
        }

        $weight = $src['mode'] == \Ess\M2ePro\Model\Amazon\Template\Description\Definition::WEIGHT_MODE_CUSTOM_VALUE
            ? $src['custom_value']
            : $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);

        if ($weight === '') {
            return '';
        }

        $weight = str_replace(',', '.', $weight);
        $weight = round((float)$weight, 2);

        return $weight;
    }

    private function getWeightUnitOfMeasure($weightType)
    {
        $src = null;

        switch ($weightType) {
            case self::WEIGHT_TYPE_ITEM_DIMENSIONS:
                $src = $this->getDescriptionDefinitionTemplate()->getItemDimensionsWeightUnitOfMeasureSource();
                break;

            case self::WEIGHT_TYPE_PACKAGE:
                $src = $this->getDescriptionDefinitionTemplate()->getPackageWeightUnitOfMeasureSource();
                break;

            case self::WEIGHT_TYPE_SHIPPING:
                $src = $this->getDescriptionDefinitionTemplate()->getShippingWeightUnitOfMeasureSource();
                break;
        }

        if (!$src) {
            return null;
        }

        $tValue = \Ess\M2ePro\Model\Amazon\Template\Description\Definition::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE;
        if ($src['mode'] == $tValue) {
            return $src['custom_value'];
        }

        $tValue =\Ess\M2ePro\Model\Amazon\Template\Description\Definition::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE;
        if ($src['mode'] == $tValue) {
            return trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
        }

        return null;
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Magento\Product\Image|null
     */
    public function getMainImage()
    {
        $image = null;

        if ($this->getDescriptionDefinitionTemplate()->isImageMainModeProduct()) {
            $image = $this->getMagentoProduct()->getImage('image');
        }

        if ($this->getDescriptionDefinitionTemplate()->isImageMainModeAttribute()) {
            $src = $this->getDescriptionDefinitionTemplate()->getImageMainSource();
            $image = $this->getMagentoProduct()->getImage($src['attribute']);
        }

        return $image;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Image[]
     */
    public function getGalleryImages()
    {
        if ($this->getDescriptionDefinitionTemplate()->isImageMainModeNone()) {
            return [];
        }

        if (!$mainImage = $this->getMainImage()) {
            return [];
        }

        if ($this->getDescriptionDefinitionTemplate()->isGalleryImagesModeNone()) {
            return [$mainImage];
        }

        $galleryImages = [];
        $gallerySource = $this->getDescriptionDefinitionTemplate()->getGalleryImagesSource();
        $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

        if ($this->getDescriptionDefinitionTemplate()->isGalleryImagesModeProduct()) {
            $limitGalleryImages = (int)$gallerySource['limit'];
            $galleryImagesTemp = $this->getMagentoProduct()->getGalleryImages($limitGalleryImages + 1);

            foreach ($galleryImagesTemp as $image) {
                if (array_key_exists($image->getHash(), $galleryImages)) {
                    continue;
                }

                $galleryImages[$image->getHash()] = $image;
            }
        }

        if ($this->getDescriptionDefinitionTemplate()->isGalleryImagesModeAttribute()) {
            $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

            $galleryImagesTemp = $this->getMagentoProduct()->getAttributeValue($gallerySource['attribute']);
            $galleryImagesTemp = (array)explode(',', $galleryImagesTemp);

            foreach ($galleryImagesTemp as $tempImageLink) {
                $tempImageLink = trim($tempImageLink);
                if (empty($tempImageLink)) {
                    continue;
                }

                /** @var \Ess\M2ePro\Model\Magento\Product\Image $image */
                $image = $this->modelFactory->getObject('Magento_Product_Image');
                $image->setUrl($tempImageLink);
                $image->setStoreId($this->getMagentoProduct()->getStoreId());

                if (array_key_exists($image->getHash(), $galleryImages)) {
                    continue;
                }

                $galleryImages[$image->getHash()] = $image;
            }
        }

        unset($galleryImages[$mainImage->getHash()]);

        if (count($galleryImages) <= 0) {
            return [$mainImage];
        }

        $galleryImages = array_slice($galleryImages, 0, $limitGalleryImages);
        array_unshift($galleryImages, $mainImage);

        return $galleryImages;
    }

    /**
     * @return \Ess\M2ePro\Model\Magento\Product\Image[]
     */
    public function getVariationDifferenceImages()
    {
        if ($this->getDescriptionDefinitionTemplate()->isImageVariationDifferenceModeNone()) {
            return [];
        }

        $image = null;

        if ($this->getDescriptionDefinitionTemplate()->isImageVariationDifferenceModeProduct()) {
            $image = $this->getMagentoProduct()->getImage('image');
        }

        if ($this->getDescriptionDefinitionTemplate()->isImageVariationDifferenceModeAttribute()) {
            $src = $this->getDescriptionDefinitionTemplate()->getImageVariationDifferenceSource();
            $image = $this->getMagentoProduct()->getImage($src['attribute']);
        }

        if (!$image) {
            return [];
        }

        return [$image];
    }

    protected function getMagentoProductAttributeValue($attributeCode, $store)
    {
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($attributeCode);

        if (empty($attributeValue) || $store === null) {
            return $attributeValue;
        }

        $isPriceConvertEnabled = $this->getHelper('Module')->getConfig()->getGroupValue(
            '/magento/attribute/',
            'price_type_converting'
        );

        if ($isPriceConvertEnabled &&
            $this->getHelper('Magento\Attribute')->isAttributeInputTypePrice($attributeCode)) {
            $currency = $this->getDescriptionDefinitionTemplate()
                ->getAmazonDescriptionTemplate()
                ->getMarketplace()
                ->getChildObject()
                ->getCurrency();

            return $this->modelFactory->getObject('Currency')->convertPrice($attributeValue, $currency, $store);
        }

        return $attributeValue;
    }

    //########################################
}
