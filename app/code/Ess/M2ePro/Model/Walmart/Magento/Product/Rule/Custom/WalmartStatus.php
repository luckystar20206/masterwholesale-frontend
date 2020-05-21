<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Walmart\Magento\Product\Rule\Custom;

/**
 * Class WalmartStatus
 * @package Ess\M2ePro\Model\Walmart\Magento\Product\Rule\Custom
 */
class WalmartStatus extends \Ess\M2ePro\Model\Magento\Product\Rule\Custom\AbstractModel
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'walmart_status';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->helperFactory->getObject('Module\Translation')->__('Status');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        $status = $product->getData('walmart_status');
        $variationChildStatuses = $product->getData('variation_child_statuses');

        if ($product->getData('is_variation_parent') && !empty($variationChildStatuses)) {
            $status = $this->getHelper('Data')->jsonDecode($variationChildStatuses);
        }

        return $status;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            [
                'value' => \Ess\M2ePro\Model\Listing\Product::STATUS_UNKNOWN,
                'label' => $this->helperFactory->getObject('Module\Translation')->__('Unknown'),
            ],
            [
                'value' => \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED,
                'label' => $this->helperFactory->getObject('Module\Translation')->__('Not Listed'),
            ],
            [
                'value' => \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED,
                'label' => $this->helperFactory->getObject('Module\Translation')->__('Active'),
            ],
            [
                'value' => \Ess\M2ePro\Model\Listing\Product::STATUS_STOPPED,
                'label' => $this->helperFactory->getObject('Module\Translation')->__('Inactive'),
            ],
            [
                'value' => \Ess\M2ePro\Model\Listing\Product::STATUS_BLOCKED,
                'label' => $this->helperFactory->getObject('Module\Translation')->__('Inactive (Blocked)'),
            ],
        ];
    }

    //########################################
}
