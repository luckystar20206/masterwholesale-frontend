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

namespace Mageplaza\Osc\Model\System\Config\Source;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\Factory;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\Osc\Helper\Data as OscHelper;

/**
 * Class PaymentMethods
 * @package Mageplaza\Osc\Model\System\Config\Source
 */
class PaymentMethods implements ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Factory
     */
    protected $_paymentMethodFactory;

    /**
     * @var OscHelper
     */
    protected $_oscHelper;

    /**
     * PaymentMethods constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Factory $paymentMethodFactory
     * @param OscHelper $oscHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Factory $paymentMethodFactory,
        OscHelper $oscHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_paymentMethodFactory = $paymentMethodFactory;
        $this->_oscHelper = $oscHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [['label' => __('-- Please select --'), 'value' => '']];

        $payments = $this->getActiveMethods();
        foreach ($payments as $paymentCode => $paymentModel) {
            $options[$paymentCode] = [
                'label' => $paymentModel->getTitle(),
                'value' => $paymentCode
            ];
        }

        return $options;
    }

    /**
     * Get all active payment method
     *
     * @return array
     */
    public function getActiveMethods()
    {
        $methods = [];
        $paymentConfig = $this->_scopeConfig->getValue('payment', ScopeInterface::SCOPE_STORE, null);
        if ($this->_oscHelper->isEnabledMultiSafepay()) {
            $paymentConfig = array_merge(
                $this->_scopeConfig->getValue('payment', ScopeInterface::SCOPE_STORE, null),
                $this->_scopeConfig->getValue('gateways', ScopeInterface::SCOPE_STORE, null)
            );
        }

        foreach ($paymentConfig as $code => $data) {
            if (isset($data['active'], $data['model']) && (bool) $data['active']) {
                try {
                    $methodModel = $this->_paymentMethodFactory->create($data['model']);
                    if (is_object($methodModel)) {
                        $methodModel->setStore(null);
                        if ($methodModel->getConfigData('active', null)) {
                            $methods[$code] = $methodModel;
                        }
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        return $methods;
    }
}
