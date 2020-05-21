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

namespace Mageplaza\Osc\Block\Checkout;

use Amazon\Core\Helper\Data;
use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Options;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\Form\AttributeMapper;
use Mageplaza\Osc\Helper\Address as OscHelper;

/**
 * Class LayoutProcessor
 * @package Mageplaza\Osc\Block\Checkout
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var OscHelper
     */
    private $_oscHelper;

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var AttributeMerger
     */
    protected $merger;

    /**
     * @var Options
     */
    private $options;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * LayoutProcessor constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param OscHelper $oscHelper
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OscHelper $oscHelper,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger
    ) {
        $this->checkoutSession               = $checkoutSession;
        $this->_oscHelper                    = $oscHelper;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper               = $attributeMapper;
        $this->merger                        = $merger;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     * @throws LocalizedException
     */
    public function process($jsLayout)
    {
        if (!$this->_oscHelper->isOscPage()) {
            return $jsLayout;
        }

        if (!isset($jsLayout['components']['checkout']['children']['steps'])) {
            return $jsLayout;
        }

        $steps = &$jsLayout['components']['checkout']['children']['steps']['children'];

        $shippingStep = &$steps['shipping-step']['children'];

        /** Shipping address fields */
        if (isset($shippingStep['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
            $shipping = &$shippingStep['shippingAddress']['children'];

            $shipping['shipping-address-fieldset']['children'] = $this->getAddressFieldset(
                $shipping['shipping-address-fieldset']['children'],
                'shippingAddress'
            );

            if ($this->_oscHelper->isEnableAmazonPay()) {
                $shippingConfig = &$shippingStep['shippingAddress'];

                $shippingConfig['component']                               = 'Mageplaza_Osc/js/view/shipping';
                $shippingConfig['children']['customer-email']['component'] = 'Mageplaza_Osc/js/view/form/element/email';
            }

            /** Fix the issue of the unsaved vat_id field */
            if (isset($shipping['shipping-address-fieldset']['children']['taxvat'])) {
                $shipping['shipping-address-fieldset']['children']['taxvat']['dataScope'] = 'shippingAddress.vat_id';
            }
        }

        /** Billing address fields */
        if (isset($shippingStep['billingAddress']['children']['billing-address-fieldset']['children'])) {
            $billing = &$shippingStep['billingAddress']['children'];

            $billing['billing-address-fieldset']['children'] = $this->getAddressFieldset(
                $billing['billing-address-fieldset']['children'],
                'billingAddress'
            );

            /** Fix the issue of the unsaved vat_id field */
            if (isset($billing['billing-address-fieldset']['children']['taxvat'])) {
                $billing['billing-address-fieldset']['children']['taxvat']['dataScope'] = 'billingAddress.vat_id';
            }

            /** Remove billing customer email if quote is not virtual */
            if (!$this->checkoutSession->getQuote()->isVirtual()) {
                unset($billing['customer-email']);
            }
        }

        $billingStep = &$steps['billing-step']['children'];

        /** Remove billing address in payment method content */
        /** @var array $fields */
        $fields = &$billingStep['payment']['children']['payments-list']['children'];
        foreach ($fields as $code => $field) {
            if ($field['component'] === 'Magento_Checkout/js/view/billing-address') {
                unset($fields[$code]);
            }
        }

        $this->applyOAFieldPosition($jsLayout);

        return $jsLayout;
    }

    /**
     * Get address fieldset for shipping/billing address
     *
     * @param $fields
     * @param $type
     *
     * @return array
     * @throws LocalizedException
     */
    public function getAddressFieldset($fields, $type)
    {
        $elements = $this->getAddressAttributes($fields);

        $systemAttribute = $elements['default'];
        if (count($systemAttribute)) {
            $attributesToConvert = [
                'prefix' => [$this->getOptions(), 'getNamePrefixOptions'],
                'suffix' => [$this->getOptions(), 'getNameSuffixOptions'],
            ];
            $systemAttribute     = $this->convertElementsToSelect($systemAttribute, $attributesToConvert);
            $fields              = $this->merger->merge(
                $systemAttribute,
                'checkoutProvider',
                $type,
                $fields
            );
        }

        $customAttribute = $elements['custom'];
        if (count($customAttribute)) {
            $fields = $this->merger->merge(
                $customAttribute,
                'checkoutProvider',
                $type . '.custom_attributes',
                $fields
            );
        }

        $fieldPosition = $this->_oscHelper->getAddressFieldPosition();

        $oscField        = [];
        $allFieldSection = $this->_oscHelper->getSortedField(false);
        foreach ($allFieldSection as $allField) {
            /** @var Attribute $field */
            foreach ($allField as $field) {
                $oscField[] = $field->getAttributeCode();
            }
        }

        $this->addCustomerAttribute($fields, $type);
        $this->rewriteFieldStreet($fields);
        $this->addAddressOption($fields, $fieldPosition, $oscField);

        // apply Custom Field label config
        for ($i = 1; $i <= 3; $i++) {
            $key = 'mposc_field_' . $i;
            if (isset($fields[$key])) {
                $fields[$key]['label'] = $this->_oscHelper->getCustomFieldLabel($i);
            }
        }

        /**
         * Compatible Amazon Pay
         */
        if ($this->_oscHelper->isEnableAmazonPay()) {
            /** @var Data $amazonHelper */
            $amazonHelper = $this->_oscHelper->getObject(Data::class);
            if ($amazonHelper->isPwaEnabled()) {
                $fields['inline-form-manipulator'] = [
                    'component' => 'Mageplaza_Osc/js/view/amazon'
                ];
            }
        }

        return $fields;
    }

    /**
     * Add customer attribute like gender, dob, taxvat
     *
     * @param $fields
     * @param $type
     *
     * @return $this
     * @throws LocalizedException
     */
    private function addCustomerAttribute(&$fields, $type)
    {
        $attributes      = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            'customer_account_create'
        );
        $addressElements = [];
        foreach ($attributes as $attribute) {
            if ($this->_oscHelper->isCustomerAttributeVisible($attribute)) {
                $addressElements[$attribute->getAttributeCode()] = $this->attributeMapper->map($attribute);
            }
        }

        if (count($addressElements)) {
            $fields = $this->merger->merge(
                $addressElements,
                'checkoutProvider',
                $type . '.custom_attributes',
                $fields
            );
        }

        foreach ($fields as $code => &$field) {
            if (isset($field['label'])) {
                $field['label'] = __($field['label']);
            }
        }

        return $this;
    }

    /**
     * @param array $fields
     * @param array $fieldPosition
     * @param array $oscField
     *
     * @return $this
     */
    private function addAddressOption(&$fields, $fieldPosition, $oscField = [])
    {
        foreach ($fields as $code => &$field) {
            if (empty($fieldPosition[$code])) {
                if ($code === 'country_id') {
                    $field['config']['additionalClasses'] = 'mp-hidden';
                    continue;
                }

                if (in_array($code, $oscField, true) || $this->_oscHelper->isEnableCustomerAttributes()) {
                    unset($fields[$code]);
                }

                continue;
            }

            $fieldConfig = $fieldPosition[$code];

            if (in_array($code, $oscField, true)) {
                $field['sortOrder'] = $fieldConfig['sortOrder'];
            }

            $classes = $field['config']['additionalClasses'] ?? '';
            $classes .= ' col-mp mp-' . $fieldConfig['colspan'];
            if ($fieldConfig['isNewRow']) {
                $classes .= ' mp-clear';
            }

            if (in_array($code, ['dob', 'mposc_field_3'], true)) {
                $classes            .= ' date';
                $field['component'] = 'Mageplaza_Osc/js/view/form/element/date';
                $field['options']   = [
                    'changeMonth' => true,
                    'changeYear'  => true,
                    'showOn'      => 'both',
                ];

                if ($code === 'code') {
                    $field['options']['yearRange'] = '-120y:c+nn';
                    $field['options']['maxDate']   = '-1d';
                }
            }

            if (isset($fieldConfig['required'])) {
                if ($fieldConfig['required']) {
                    $classes .= ' required';

                    $field['validation']['required-entry'] = true;
                } else {
                    $classes .= ' not-required';

                    $validation = &$field['validation'];
                    if (isset($validation['required-entry'])) {
                        unset($validation['required-entry']);
                    }
                    if (isset($validation['min_text_length'])) {
                        unset($validation['min_text_length']);
                    }
                }
            }

            $field['config']['additionalClasses'] = $classes;

            $this->rewriteTemplate($field, $fieldConfig);
        }

        unset($field);

        return $this;
    }

    /**
     * Change template to remove valueUpdate = 'keyup'
     *
     * @param array $field
     * @param array $fieldConfig
     * @param string $template
     *
     * @return $this
     */
    private function rewriteTemplate(&$field, $fieldConfig, $template = 'Mageplaza_Osc/container/form/element/input')
    {
        $elementTmpl = '';
        if (isset($field['config']['elementTmpl'])) {
            $elementTmpl = &$field['config']['elementTmpl'];
        }
        if (isset($field['type']) && $field['type'] === 'group') {
            foreach ($field['children'] as $key => &$child) {
                $classes = $child['config']['additionalClasses'] ?? '';

                if ($key) {
                    $classes .= ' additional';
                }

                if (isset($fieldConfig['required']) && !$fieldConfig['required']) {
                    $field['config']['additionalClasses'] .= ' not-required';

                    $classes .= ' not-required';

                    $validation = &$child['validation'];
                    if (isset($validation['required-entry'])) {
                        unset($validation['required-entry']);
                    }
                    if (isset($validation['min_text_length'])) {
                        unset($validation['min_text_length']);
                    }
                }

                $child['config']['additionalClasses'] = $classes;
                if ($key === 0 &&
                    $this->_oscHelper->isGoogleHttps() &&
                    in_array('street', explode('.', $field['dataScope']), true)
                ) {
                    $this->rewriteTemplate($child, $fieldConfig, 'Mageplaza_Osc/container/form/element/street');
                    continue;
                }
                $this->rewriteTemplate($child, $fieldConfig);
            }
        } elseif (in_array($elementTmpl, ['ui/form/element/input', 'ui/form/element/date'], true)) {
            if ($elementTmpl === 'ui/form/element/input') {
                $elementTmpl = $template;
            }
            if ($this->_oscHelper->isUsedMaterialDesign()) {
                $field['config']['template'] = 'Mageplaza_Osc/container/form/field';
            }
        }

        return $this;
    }

    /**
     * Change template street when enable material design
     *
     * @param $fields
     *
     * @return $this
     */
    private function rewriteFieldStreet(&$fields)
    {
        if ($this->_oscHelper->isUsedMaterialDesign()) {
            $fields['country_id']['config']['template'] = 'Mageplaza_Osc/container/form/field';
            $fields['region_id']['config']['template']  = 'Mageplaza_Osc/container/form/field';
            foreach ($fields['street']['children'] as $key => $value) {
                $fields['street']['children'][0]['label']                 = $fields['street']['label'];
                $fields['street']['children'][$key]['config']['template'] = 'Mageplaza_Osc/container/form/field';
            }
            $fields['street']['config']['fieldTemplate'] = 'Mageplaza_Osc/container/form/field';
            unset($fields['street']['label']);
        }

        return $this;
    }

    /**
     * @return Options
     */
    private function getOptions()
    {
        if (!is_object($this->options)) {
            $this->options = ObjectManager::getInstance()->get(Options::class);
        }

        return $this->options;
    }

    /**
     * @param array $fields
     *
     * @return array
     * @throws LocalizedException
     */
    private function getAddressAttributes($fields)
    {
        $elements  = [
            'custom'  => [],
            'default' => []
        ];
        $formCodes = ['onestepcheckout_index_index', 'customer_register_address'];
        foreach ($formCodes as $formCode) {
            $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
                'customer_address',
                $formCode
            );

            /** @var Attribute $attribute */
            foreach ($attributes as $attribute) {
                $code = $attribute->getAttributeCode();

                if (isset($elements['custom'][$code]) || isset($elements['default'][$code])) {
                    continue;
                }

                $element = $this->attributeMapper->map($attribute);
                if (isset($element['label'])) {
                    $label            = $element['label'];
                    $element['label'] = __($label);
                }

                if ($attribute->getIsUserDefined()) {
                    if (!isset($fields[$code])) {
                        $elements['custom'][$code] = $element;
                    }
                } else {
                    $elements['default'][$code] = $element;
                }
            }
        }

        return $elements;
    }

    /**
     * Convert elements(like prefix and suffix) from inputs to selects when necessary
     *
     * @param array $elements address attributes
     * @param array $attributesToConvert fields and their callbacks
     *
     * @return array
     */
    private function convertElementsToSelect($elements, $attributesToConvert)
    {
        $codes = array_keys($attributesToConvert);
        foreach (array_keys($elements) as $code) {
            if (!in_array($code, $codes, true)) {
                continue;
            }
            $options = call_user_func($attributesToConvert[$code]);
            if (!is_array($options)) {
                continue;
            }
            $elements[$code]['dataType']    = 'select';
            $elements[$code]['formElement'] = 'select';

            foreach ($options as $key => $value) {
                $elements[$code]['options'][] = [
                    'value' => $key,
                    'label' => $value,
                ];
            }
        }

        return $elements;
    }

    /**
     * @param array $jsLayout
     */
    private function applyOAFieldPosition(&$jsLayout)
    {
        if (!$this->_oscHelper->isEnableOrderAttributes()) {
            return;
        }

        $shipping = &$jsLayout['components']['checkout']['children']['steps']['children']
        ['shipping-step']['children']['shippingAddress']['children'];
        $payment  = &$jsLayout['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children'];
        $summary  = &$jsLayout['components']['checkout']['children']['sidebar']['children']
        ['place-order-information-left']['children']['addition-information']['children'];

        $this->addOAOption($shipping['before-shipping-method-form']['children']); // shipping top
        $this->addOAOption($shipping); // shipping bottom
        $this->addOAOption($payment['beforeMethods']['children']); // payment top
        $this->addOAOption($payment['afterMethods']['children']); // payment bottom
        $this->addOAOption($summary); // order summary
    }

    /**
     * @param array $fieldset
     *
     * @return $this
     */
    private function addOAOption(&$fieldset)
    {
        $position = [];
        foreach ($this->_oscHelper->getOAFieldPosition() as $item) {
            $position[$item['code']] = $item;
        }

        $fields = [];
        if (isset($fieldset['mpOrderAttributes']['children'])) {
            $fields = &$fieldset['mpOrderAttributes']['children'];
        }

        return $this->addAddressOption($fields, $position, $position);
    }
}
