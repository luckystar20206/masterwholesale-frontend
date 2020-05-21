<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Plugin\Model\Adapter;

use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Preprocessor
 * @package Mageplaza\LayeredNavigation\Model\Plugin\Adapter
 */
class Preprocessor
{
    /**
     * @type \Mageplaza\LayeredNavigation\Helper\Data
     */
    protected $_moduleHelper;

    /**
     * @type \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Preprocessor constructor.
     * @param \Mageplaza\LayeredNavigation\Helper\Data $moduleHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Mageplaza\LayeredNavigation\Helper\Data $moduleHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->_moduleHelper = $moduleHelper;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor $subject
     * @param \Closure $proceed
     * @param $filter
     * @param $isNegation
     * @param $query
     * @return string
     */
    public function aroundProcess(\Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor $subject, \Closure $proceed, $filter, $isNegation, $query)
    {
        $productMetadata = $this->objectManager->get(ProductMetadataInterface::class);
        $version         = $productMetadata->getVersion(); //will return the magento version

        if ($this->_moduleHelper->isEnabled() && ($filter->getField() === 'category_ids')) {
            if (version_compare($version, '2.1.13', '>=') && version_compare($version, '2.1.15', '<=')) {
                return 'category_products_index.category_id IN (' . $filter->getValue() . ')';
            }

            return 'category_ids_index.category_id IN (' . $filter->getValue() . ')';
        }

        return $proceed($filter, $isNegation, $query);
    }
}
