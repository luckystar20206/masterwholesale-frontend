<?php
/**
 * @category Mageants ChangeAttributeSet
 * @package Mageants_ChangeAttributeSet
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants Team <support@mageants.com>
 */
 
namespace Mageants\ChangeAttributeSet\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\EntityFactory;

class MassChangeattributeset extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * Massactions filter
     */     
    protected $filter;
    
    /**
     * ScopeConfigInterface scopeConfig
     */
    protected $scopeConfig;
  
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
   
    /** 
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

	/**
	 *  @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable 
	 */
    protected $configurableProduct;

    /** 
     * @var \Magento\Eav\Model\EntityFactory 
     */
    protected $entityFactory;
    
    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SetFactory $attributeSetFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Configurable $configurableProduct
     * @param EntityFactory $entityFactory
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SetFactory $attributeSetFactory,
        ScopeConfigInterface $scopeConfig,
        Configurable $configurableProduct,
        EntityFactory $entityFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->scopeConfig = $scopeConfig;
        $this->configurableProduct = $configurableProduct;
        $this->entityFactory = $entityFactory;
        parent::__construct($context, $productBuilder);
        
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getAllIds();
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $attributeSetId = (int) $this->getRequest()->getParam('changeattributeset'); 
        try {       
             foreach ($collection->getItems() as $product) {
				if($this->validateConfigurable($product,$attributeSetId,$storeId) == false)
				{
					$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
					return $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
					break;
                }
				$product->setAttributeSetId($attributeSetId)->setStoreId($storeId);               
             }
             
            $collection->save();            
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', count($productIds)));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while updating the product(s) atrribute set.'));
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
    }
    private function validateConfigurable($product,$attributeSetId,$storeId)
    { 
		$type = $product->getTypeInstance();
		if(!$type instanceof Configurable)
		{
			return true;
		}
        $attributeSet = $this->attributeSetFactory->create()->load($attributeSetId);
        $attributes = $this->configurableProduct->getUsedProductAttributes($product);
        $attributeSet->addSetInfo(
            $this->entityFactory->create()->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId(),
            $attributes
        );
        foreach ($type->getConfigurableAttributes($product) as $configAattribute) {
            $attribute  = $configAattribute->getProductAttribute();
            if (!is_null($attribute)) {
				if (!$attribute->isInSet($attributeSetId)) {
					$attribute->setAttributeSetId(
						$attributeSetId
					)->setAttributeGroupId(
						$attributeSet->getDefaultGroupId($attributeSetId)
					)->save();
					return true;
			}
				else
				{
					return true;
				}
            }
        }
    } 
}
