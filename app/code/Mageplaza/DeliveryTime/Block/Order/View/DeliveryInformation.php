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

namespace Mageplaza\DeliveryTime\Block\Order\View;

use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\DeliveryTime\Helper\Data as MpDtHelper;

/**
 * Class Comment
 * @package Mageplaza\DeliveryTime\Block\Order\View
 */
class DeliveryInformation extends Template
{
    /**
     * @type Registry|null
     */
    protected $registry = null;

    /**
     * @var MpDtHelper
     */
    protected $mpDtHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param MpDtHelper $mpDtHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        MpDtHelper $mpDtHelper,
        array $data = []
    ) {
        $this->registry   = $registry;
        $this->mpDtHelper = $mpDtHelper;

        parent::__construct($context, $data);
    }

    /**
     * Get delivery information
     *
     * @return DataObject
     */
    public function getDeliveryInformation()
    {
        $result = [];

        if ($order = $this->getOrder()) {
            $deliveryInformation = $order->getMpDeliveryInformation();

            if (is_array(json_decode($deliveryInformation, true))) {
                $result = json_decode($deliveryInformation, true);
            } else {
                $values = explode(' ', $deliveryInformation);
                if (sizeof($values) > 1) {
                    $result['deliveryDate'] = $values[0];
                    $result['deliveryTime'] = $values[1];
                }

                $result['houseSecurityCode'] = $order->getOscOrderHouseSecurityCode();
            }
        }

        return new DataObject($result);
    }

    /**
     * Get current order
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }
}
