<?php
namespace Smartwave\Porto\Model\Config\Settings\General;

class Boxed implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [['value' => 'wide', 'label' => __('Wide (Default)')], ['value' => 'boxed', 'label' => __('Boxed')]];
    }

    public function toArray()
    {
        return ['wide' => __('Wide (Default)'), 'boxed' => __('Boxed')];
    }
}
