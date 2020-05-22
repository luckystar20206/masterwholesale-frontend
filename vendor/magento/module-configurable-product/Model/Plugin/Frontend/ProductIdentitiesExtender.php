<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Plugin\Frontend;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;

/**
 *  Extender of product identities for child of configurable products
 */
class ProductIdentitiesExtender
{
    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @param Configurable $configurableType
     */
    public function __construct(Configurable $configurableType)
    {
        $this->configurableType = $configurableType;
    }

    /**
     * Add child identities to product identities
     *
     * @param Product $subject
     * @param array $identities
     * @return array
     */
    public function afterGetIdentities(Product $subject, array $identities): array
    {
        foreach ($this->configurableType->getChildrenIds($subject->getId()) as $childIds) {
            foreach ($childIds as $childId) {
				# 2020-05-22 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
				# "Add the `vendor/magento/module-configurable-product/Model/Plugin/Frontend/ProductIdentitiesExtender.php` file
				# (modified by someone at `2020-04-17 14:44:22 -07`) to the source control":
				# https://github.com/masterwholesale-com/site/issues/9
                //$identities[] = Product::CACHE_TAG . '_' . $childId;
            }
        }

        return array_unique($identities);
    }
}
