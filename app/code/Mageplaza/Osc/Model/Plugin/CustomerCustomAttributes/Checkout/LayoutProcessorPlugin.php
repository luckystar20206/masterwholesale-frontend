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

namespace Mageplaza\Osc\Model\Plugin\CustomerCustomAttributes\Checkout;

use Magento\CustomerCustomAttributes\Block\Checkout\LayoutProcessor;
use Mageplaza\Osc\Helper\Address;

/**
 * Class LayoutProcessorPlugin
 * @package Mageplaza\Osc\Model\Plugin\CustomerCustomAttributes\Checkout
 */
class LayoutProcessorPlugin
{
    /**
     * @var Address
     */
    private $helper;

    /**
     * LayoutProcessor constructor.
     *
     * @param Address $helper
     */
    public function __construct(Address $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     *
     * @return array
     */
    public function afterProcess(LayoutProcessor $subject, $jsLayout)
    {
        $this->processCustomAttributesForPaymentMethods($jsLayout);
        $this->mergeCustomAttributes($jsLayout);

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     */
    private function processCustomAttributesForPaymentMethods(&$jsLayout)
    {
        // The following code is a workaround for custom address attributes
        $paymentMethodRenders = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children'];
        if (!is_array($paymentMethodRenders)) {
            return;
        }

        foreach ($paymentMethodRenders as $name => &$renderer) {
            if (empty($renderer['children']) || !array_key_exists('form-fields', $renderer['children'])) {
                continue;
            }

            $fields = &$renderer['children']['form-fields']['children'];

            $this->processOscFields($fields);
        }
    }

    /**
     * @param array $jsLayout
     */
    private function mergeCustomAttributes(&$jsLayout)
    {
        if (empty($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
            return;
        }

        $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

        $this->processOscFields($fields);
    }

    /**
     * @param array $fields
     */
    private function processOscFields(&$fields)
    {
        $oscField = array_keys($this->helper->getSortedField());

        for ($i = 1; $i <= 3; $i++) {
            $key = 'mposc_field_' . $i;
            if (!in_array($key, $oscField, true) || !$this->helper->isOscPage()) {
                unset($fields[$key]);
            }
        }
    }
}
