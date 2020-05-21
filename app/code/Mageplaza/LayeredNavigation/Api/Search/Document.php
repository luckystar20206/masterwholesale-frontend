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

namespace Mageplaza\LayeredNavigation\Api\Search;

use Magento\Framework\Api\Search\Document as SourceDocument;

/**
 * Class Document
 * @package Mageplaza\LayeredNavigation\Api\Search
 */
class Document extends SourceDocument
{
    /**
     * Get Document field
     *
     * @param string $fieldName
     * @return \Magento\Framework\Api\AttributeInterface
     */
    public function getField($fieldName)
    {
        return $this->getCustomAttribute($fieldName);
    }
}
