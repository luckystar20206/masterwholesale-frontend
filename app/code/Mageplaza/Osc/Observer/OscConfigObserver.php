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

namespace Mageplaza\Osc\Observer;

use Exception;
use Magento\Config\Model\ResourceModel\Config as ModelConfig;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\GiftMessage\Helper\Message;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\Osc\Helper\Data as OscHelper;

/**
 * Class OscConfigObserver
 * @package Mageplaza\Osc\Observer
 */
class OscConfigObserver implements ObserverInterface
{
    /**
     * @var ModelConfig
     */
    private $_modelConfig;

    /**
     * @var OscHelper
     */
    private $_oscHelper;

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * OscConfigObserver constructor.
     *
     * @param ModelConfig $modelConfig
     * @param OscHelper $oscHelper
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     */
    public function __construct(
        ModelConfig $modelConfig,
        OscHelper $oscHelper,
        AttributeMetadataDataProvider $attributeMetadataDataProvider
    ) {
        $this->_modelConfig                  = $modelConfig;
        $this->_oscHelper                    = $oscHelper;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $scope   = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeId = 0;

        if ($store = $observer->getEvent()->getStore()) {
            $scope   = ScopeInterface::SCOPE_STORE;
            $scopeId = $store;
        }

        if ($website = $observer->getEvent()->getWebsite()) {
            $scope   = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $website;
        }
        $this->_modelConfig->saveConfig(
            Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER,
            !$this->_oscHelper->isDisabledGiftMessage(),
            $scope,
            $scopeId
        )->saveConfig(
            Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            $this->_oscHelper->isEnableGiftMessageItems(),
            $scope,
            $scopeId
        )->saveConfig(
            'checkout/options/enable_agreements',
            $this->_oscHelper->disabledPaymentTOC() || $this->_oscHelper->disabledReviewTOC(),
            $scope,
            $scopeId
        );

        if ($store || $website) {
            return;
        }

        for ($i = 1; $i <= 3; $i++) {
            $key  = 'mposc_field_' . $i;
            $attr = $this->attributeMetadataDataProvider->getAttribute('customer_address', $key);

            if (!$attr) {
                continue;
            }

            $label = $this->_oscHelper->getCustomFieldLabel($i);
            $attr->setDefaultFrontendLabel($label)->save();
        }
    }
}
