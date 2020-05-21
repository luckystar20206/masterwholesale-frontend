<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Walmart\Magento\Product\Rule\Custom;

/**
 * Class WalmartGtin
 * @package Ess\M2ePro\Model\Walmart\Magento\Product\Rule\Custom
 */
class WalmartGtin extends \Ess\M2ePro\Model\Magento\Product\Rule\Custom\AbstractModel
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'walmart_gtin';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->helperFactory->getObject('Module\Translation')->__('GTIN');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData('gtin');
    }

    //########################################
}
