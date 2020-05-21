<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class Description implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [['value' => '', 'label' => __('Default')], ['value' => 'full_width', 'label' => __('As Full Width below the Header')], ['value' => 'main_column', 'label' => __('Main Column above the Toolbar')]];
    }

    public function toArray()
    {
        return ['' => __('Default'), 'full_width' => __('As Full Width below the Header'), 'main_column' => __('Main Column above the Toolbar')];
    }
}
