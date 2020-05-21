<?php
namespace Smartwave\Porto\Model\Config\Settings\Footer\Middle;

class Block implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [['value' => '', 'label' => __('Do not show')], ['value' => 'custom', 'label' => __('Custom Block')], ['value' => 'newsletter', 'label' => __('Newsletter Subscribe')]];
    }

    public function toArray()
    {
        return ['' => __('Do not show'), 'custom' => __('Custom Block'), 'newsletter' => __('Newsletter Subscribe')];
    }
}
