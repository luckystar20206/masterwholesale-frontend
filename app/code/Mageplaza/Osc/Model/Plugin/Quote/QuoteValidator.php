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

namespace Mageplaza\Osc\Model\Plugin\Quote;

use Magento\Quote\Model\Quote;

/**
 * Class QuoteValidator
 * @package Mageplaza\Osc\Model\Plugin\Quote
 */
class QuoteValidator
{
    /**
     * @param \Magento\Quote\Model\QuoteValidator $subject
     * @param Quote $quote
     *
     * @return mixed
     */
    public function beforeValidateBeforeSubmit(
        \Magento\Quote\Model\QuoteValidator $subject,
        Quote $quote
    ) {
        if (!$quote->isVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
        $quote->getBillingAddress()->setShouldIgnoreValidation(true);

        return [$quote];
    }
}
