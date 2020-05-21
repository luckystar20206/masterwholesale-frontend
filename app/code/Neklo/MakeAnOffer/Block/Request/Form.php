<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Block\Request;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Neklo\MakeAnOffer\Helper\Config;
use Magento\Framework\App\Http\Context as HttpContext;
use Neklo\MakeAnOffer\Model\Source\Attribute\Product;
use Neklo\MakeAnOffer\Model\Source\Attribute\Category;

class Form extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Neklo\MakeAnOffer\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var Context
     */
    private $context;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Config $configHelper
     * @param HttpContext $httpContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $configHelper,
        HttpContext $httpContext,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->context = $context;
        $this->configHelper = $configHelper;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    /**
     * Get Store ID
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get Current Product ID
     *
     * @return int
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Get Current Product Type
     *
     * @return string
     */
    public function getProductType()
    {
        return $this->registry->registry('current_product')->getTypeId();
    }

    /**
     * Get Output type
     *
     * @return int
     */
    public function getOutputType()
    {
        return $this->configHelper->getDisplayMode($this->getStoreId());
    }

    /**
     * Get Button Label
     *
     * @return string
     */
    public function getButtonLabel()
    {
        return __($this->configHelper->getButtonLabel($this->getStoreId()));
    }

    /**
     * Get Block/Popup Title
     *
     * @return string
     */
    public function getTitle()
    {
        return __($this->configHelper->getBlocTitle($this->getStoreId()));
    }

    /**
     * Get Block/Popup Short Description
     *
     * @return string
     */
    public function getShortDescription()
    {
        return __($this->configHelper->getBlockShortDescription($this->getStoreId()));
    }

    /**
     * @return mixed|null
     */
    public function getCustomerEmail()
    {
        return $this->httpContext->getValue(\Neklo\MakeAnOffer\Model\Customer\Context::CONTEXT_CUSTOMER_EMAIL);
    }

    /**
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function canShow() // @codingStandardsIgnoreLine
    {
        if (!$this->configHelper->isModuleEnable($this->getStoreId())) {
            return false;
        }

        if ($this->getProductType() == 'bundle' || $this->getProductType() == 'grouped') {
            return false;
        }

        if ($this->getCurrentProduct()->getAllowMakeAnOfferProduct() == Product::ATTRIBUTE_YES) {
            return true;
        } elseif ($this->getCurrentProduct()->getAllowMakeAnOfferProduct() == Product::ATTRIBUTE_NO
            && $this->getCurrentProduct()->getAllowMakeAnOfferProduct() !== null) {
            return false;
        } elseif ($this->getCurrentProduct()->getAllowMakeAnOfferProduct() === null
            || $this->getCurrentProduct()->getAllowMakeAnOfferProduct()
                == Product::ATTRIBUTE_CONTROLLED_BY_CATEGORY_SELECTION
        ) {
            if ($this->getCurrentCategory() === null) {
                return true;
            }
            if ($this->getCurrentCategory()->getAllowMakeAnOfferCategory() == Category::ATTRIBUTE_NO
                && $this->getCurrentCategory()->getAllowMakeAnOfferCategory() !== null) {
                return false;
            } elseif ($this->getCurrentCategory()->getAllowMakeAnOfferCategory() == Category::ATTRIBUTE_YES) {
                return true;
            } elseif ($this->getCurrentCategory()->getAllowMakeAnOfferCategory() === null
                || $this->getCurrentCategory()->getAllowMakeAnOfferCategory()
                    == Category::ATTRIBUTE_INHERIT_FROM_PARENT_CATEGORY
            ) {
                $parentCategory = $this->getCurrentCategory();
                while ($parentCategory = $parentCategory->getParentCategory()) {
                    if ($parentCategory->getLevel() == 0) {
                        return true;
                    } elseif ($parentCategory->getAllowMakeAnOfferCategory() !== null
                        && $parentCategory->getAllowMakeAnOfferCategory()
                            != Category::ATTRIBUTE_INHERIT_FROM_PARENT_CATEGORY
                    ) {
                        return $parentCategory->getAllowMakeAnOfferCategory();
                    }
                }

                return true;
            }
        }

        return true;
    }

    /**
     * Get formkey
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->context->getFormKey()->getFormKey();
    }

    /**
     * @return int
     */
    public function getDisplayPriceWithTax()
    {
        return $this->configHelper->getDisplayPriceWithTax();
    }

    /**
     * @return int
     */
    public function canDisplayAnotherStoreLink()
    {
        return $this->configHelper->getDisplayAnotherStoreLink();
    }
}
