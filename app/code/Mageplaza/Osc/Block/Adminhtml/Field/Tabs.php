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

namespace Mageplaza\Osc\Block\Adminhtml\Field;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Mageplaza\Osc\Helper\Address;
use Mageplaza\Osc\Helper\Data;

/**
 * Class Tabs
 * @package Mageplaza\Osc\Block\Adminhtml\Field
 */
class Tabs extends Container
{
    /**
     * @var Address
     */
    protected $helper;

    /**
     * Position constructor.
     *
     * @param Context $context
     * @param Address $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Address $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addButton('save', [
            'label' => __('Save Position'),
            'class' => 'save primary mposc-save-position',
        ]);

        $caAction = "setLocation('{$this->getUrl('customer/address_attribute/new')}')";
        if (!$this->helper->isEnableCustomerAttributes()) {
            $url      = Data::CUSTOMER_ATTR_URL;
            $link     = '<a href="' . $url . '" target="_blank">Mageplaza Customer Attributes</a>';
            $message  = __('Please install %1 to add more address fields.', $link);
            $caAction = "confirmSetLocation('{$message}', '{$url}')";
        }

        $this->addButton('add_customer_attr', [
            'label'   => __('Add Customer Attributes'),
            'class'   => 'secondary',
            'onclick' => $caAction,
        ]);

        $oaAction = "setLocation('{$this->getUrl('mporderattributes/attribute/new')}')";
        if (!$this->helper->isEnableOrderAttributes()) {
            $url      = Data::ORDER_ATTR_URL;
            $link     = '<a href="' . $url . '" target="_blank">Mageplaza Order Attributes</a>';
            $message  = __('Please install %1 to add more custom checkout fields.', $link);
            $oaAction = "confirmSetLocation('{$message}', '{$url}')";
        }

        $this->addButton('add_order_attr', [
            'label'   => __('Add Order Attributes'),
            'class'   => 'secondary',
            'onclick' => $oaAction,
        ]);
    }

    /**
     * Retrieve the header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return (string) __('Manage Fields');
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('*/*/save');
    }
}
