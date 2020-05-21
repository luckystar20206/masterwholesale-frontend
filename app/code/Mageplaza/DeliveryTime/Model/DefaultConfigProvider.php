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
 * @package     Mageplaza_DeliveryTime
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DeliveryTime\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\DeliveryTime\Helper\Data as MpDtHelper;
use Zend_Serializer_Exception;

/**
 * Class DefaultConfigProvider
 * @package Mageplaza\DeliveryTime\Model
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var MpDtHelper
     */
    protected $mpDtHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * DefaultConfigProvider constructor.
     *
     * @param MpDtHelper $mpDtHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        MpDtHelper $mpDtHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->mpDtHelper   = $mpDtHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        if (!$this->mpDtHelper->isEnabled()) {
            return [];
        }

        return ['mpDtConfig' => $this->getMpDtConfig()];
    }

    /**
     * @return array
     * @throws Zend_Serializer_Exception
     */
    private function getMpDtConfig()
    {
        return [
            'isEnabledDeliveryTime'      => $this->mpDtHelper->isEnabledDeliveryTime(),
            'isEnabledHouseSecurityCode' => $this->mpDtHelper->isEnabledHouseSecurityCode(),
            'isEnabledDeliveryComment'   => $this->mpDtHelper->isEnabledDeliveryComment(),
            'deliveryDateFormat'         => $this->mpDtHelper->getDateFormat(),
            'deliveryDaysOff'            => $this->mpDtHelper->getDaysOff(),
            'deliveryDateOff'            => $this->mpDtHelper->getDateOff(),
            'deliveryTime'               => $this->mpDtHelper->getDeliveryTIme()
        ];
    }
}
