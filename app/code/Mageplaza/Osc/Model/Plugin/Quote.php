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
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Model\Plugin;

use Closure;

/**
 * Class Quote
 * @package Mageplaza\Osc\Model\Plugin
 */
class Quote
{
    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param Closure $process
     * @param $itemId
     *
     * @return bool|mixed
     */
    public function aroundGetItemById(\Magento\Quote\Model\Quote $subject, Closure $process, $itemId)
    {
        foreach ($subject->getItemsCollection() as $item) {
            if ($item->getId() == $itemId) {
                return $item;
            }
        }

        return false;
    }
}
