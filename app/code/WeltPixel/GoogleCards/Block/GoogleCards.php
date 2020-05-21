<?php
namespace WeltPixel\GoogleCards\Block;

class GoogleCards extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $_imageBuilder;

    /**
     * @var \WeltPixel\GoogleCards\Helper\Data
     */
    protected $_helper;

    /**
     * Block factory
     *
     * @var \Magento\Review\Model\Review\SummaryFactory
     */
    protected $_reviewSummaryFactory;


    /**
     * @param \Magento\Catalog\Block\Product\Context $productContext
     * @param \WeltPixel\GoogleCards\Helper\Data $helper
     * @param \Magento\Review\Model\Review\SummaryFactory $reviewSummaryFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Catalog\Block\Product\Context $productContext,
                                \WeltPixel\GoogleCards\Helper\Data $helper,
                                \Magento\Review\Model\Review\SummaryFactory $reviewSummaryFactory,
                                \Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        $this->_coreRegistry = $productContext->getRegistry();
        $this->_helper = $helper;
        $this->_reviewSummaryFactory = $reviewSummaryFactory;
        $this->_imageBuilder = $productContext->getImageBuilder();
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getDescription($product)
    {
        if ($this->_helper->getDescriptionType()) {
            return nl2br($product->getData('description'));
        } else {
            return nl2br($product->getData('short_description'));
        }
    }


    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getBrand($product)
    {
        $brandAttribute = $this->_helper->getBrand();
        $brandName = '';
        if ($brandAttribute) {
            try {
                $brandName = $product->getAttributeText($brandAttribute);
                if (is_array($brandName) || !$brandName) {
                    $brandName = $product->getData($brandAttribute);
                }
            } catch (\Exception $ex) {
                $brandName = '';
            }
        }
        return $brandName;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getSku($product)
    {
        $skuAttribute = $this->_helper->getSku();
        $sku = '';
        if ($skuAttribute) {
            try {
                $sku = $product->getAttributeText($skuAttribute);
                if (is_array($sku) || !$sku) {
                    $sku = $product->getData($skuAttribute);
                }
            } catch (\Exception $ex) {
                $sku = '';
            }
        }
        return $sku;
    }

    /**
     * @return \Magento\Review\Model\Review\Summary
     */
    public function getReviewSummary()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $reviewSummary = $this->_reviewSummaryFactory->create();
        $reviewSummary->setData('store_id', $storeId);
        $summaryModel = $reviewSummary->load($this->getProduct()->getId());

        return $summaryModel;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        $priceOption = $this->_helper->getGoogleCardsPrice();
        return $this->_calculatePrice($priceOption);
    }

    /**
     * @param string $priceOption
     * @return float
     */
    protected function _calculatePrice($priceOption) {
        $priceInfo = $this->getProduct()->getPriceInfo()->getPrice('final_price')->getAmount();
        $price = $priceInfo->getValue();
        /** Display of both prices incl. tax and excl. tax */
        if ((int)$this->_scopeConfig->getValue(
                'tax/display/type',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 3
        ) {
            switch ($priceOption) {
                case 'incl_tax':
                    $price = $priceInfo->getValue();
                    break;
                case 'excl_tax' :
                    $price = $priceInfo->getValue('tax');
                    break;
            }
        }

        return number_format($price, 2, '.', '');
    }


}