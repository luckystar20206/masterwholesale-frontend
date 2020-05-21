<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Neklo\MakeAnOffer\Model\Request;
use Neklo\MakeAnOffer\Model\Source\Status;

class Data extends AbstractHelper
{

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Context $context
     * @param Emulation $emulation
     * @param Image $imageHelper
     * @param ProductFactory $productFactory
     * @param Status $status
     * @param TimezoneInterface $timezone
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        Emulation $emulation,
        Image $imageHelper,
        ProductFactory $productFactory,
        Status $status,
        TimezoneInterface $timezone,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->emulation = $emulation;
        $this->imageHelper = $imageHelper;
        $this->productFactory = $productFactory;
        $this->status = $status;
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * @param Request $model
     * @return Request
     */
    public function prepareDataModel(Request $model)
    {
        $model->setStatusName($this->status->getArray()[$model->getStatus()]);
        $model->setCreatedAt($this->timezone->formatDateTime($model->getCreatedAt(), \IntlDateFormatter::MEDIUM));

        return $model;
    }

    /**
     * @param $productId
     * @param $storeId
     * @return array
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function getStoreProductData($requestItem)
    {
        $storeId = $requestItem->getStoreId();
        $productId = $requestItem->getProductId();
        $this->emulation->startEnvironmentEmulation(
            $storeId,
            Area::AREA_FRONTEND
        );

        $product = $this->productRepository->getById($productId);

        $parentProducts = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class)
            ->getParentIdsByChild($product->getId());

        $productOptions = [];

        $routeParams = [ '_nosid' => true, '_query' => ['___store' => 'rus']];
        $routeParams['id'] = $product->getId();
        $routeParams['s'] = $product->getUrlKey();
        $code = $this->storeManager->getStore()->getCode();

        if (isset($parentProducts[0])) {
            $parent = $this->productFactory->create()->load($parentProducts[0]);
            $parent = $this->productRepository->getById($parentProducts[0]);

            $productUrl = $parent->getUrlModel()->getUrl($parent);
            $productName = $parent->getName();

            $attributes = $parent->getTypeInstance()->getConfigurableAttributes($parent);
            $i = 0;
            foreach ($attributes->getItems() as $attribute) {
                $attributeLabel = $attribute->getLabel();
                $attributeCode = $attribute->getProductAttribute()->getAttributeCode();
                $attributeValue = $product->getData($attributeCode);
                foreach ($attribute->getOptions() as $option) {
                    if ($option['value_index'] == $attributeValue) {
                        $optionLabel = $option['store_label'];
                    }
                }
                $productOptions[$i]['attribute'] = $attributeLabel;
                $productOptions[$i]['option'] = $optionLabel;
                $i++;
            }
        } else {
            $productUrl = $product->getUrlModel()->getUrl($product);
            $productName = $product->getName();
        }

        $imageUrl = $this->imageHelper->init(
            $product,
            'small_image',
            ['type' => 'small_image']
        )
            ->keepAspectRatio(true)->resize('200', '200')->getUrl();

        $this->emulation->stopEnvironmentEmulation();

        $productStoreData = [
            'product_name' => $productName,
            'product_url' => $productUrl,
            'image_url' => $imageUrl,
            'product_options' => $productOptions,
        ];

        if ($requestItem->getStatus() == Status::ACCEPTED_REQUEST_STATUS) {
            $applyUrl = $this->_urlBuilder->getUrl(
              'makeanoffer/index/apply',
                [
                    'id' => $requestItem->getId(),
                    '_nosid' => true
                ]
            );
            $productStoreData['apply_url'] = $applyUrl;
        }

        return $productStoreData;
    }
}
