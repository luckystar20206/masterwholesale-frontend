<?php
namespace Smartwave\Porto\Block;

class RickSnippet extends \Magento\Framework\View\Element\Template {
    protected $_coreRegistry;
    protected $_imageBuilder;
    protected $_reviewSummaryFactory;
    public function __construct(\Magento\Catalog\Block\Product\Context $productContext,
                                \Magento\Review\Model\Review\SummaryFactory $reviewSummaryFactory,
                                \Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        $this->_coreRegistry = $productContext->getRegistry();
        $this->_reviewSummaryFactory = $reviewSummaryFactory;
        $this->_imageBuilder = $productContext->getImageBuilder();
        parent::__construct($context, $data);
    }
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
    public function getReviewSummary()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $reviewSummary = $this->_reviewSummaryFactory->create();
        $reviewSummary->setData('store_id', $storeId);
        $summaryModel = $reviewSummary->load($this->getProduct()->getId());

        return $summaryModel;
    }
    public function getCurrencyCode() {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
}