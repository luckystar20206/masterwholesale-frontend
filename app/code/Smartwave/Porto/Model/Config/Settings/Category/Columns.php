<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class Columns implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [['value' => '2', 'label' => __('2 Columns')], ['value' => '3', 'label' => __('3 Columns')], ['value' => '4', 'label' => __('4 Columns')], ['value' => '5', 'label' => __('5 Columns')], ['value' => '6', 'label' => __('6 Columns')], ['value' => '7', 'label' => __('7 Columns')], ['value' => '8', 'label' => __('8 Columns')]];
    }

    public function toArray()
    {
        return ['2' => __('2 Columns'), '3' => __('3 Columns'), '4' => __('4 Columns'), '5' => __('5 Columns'), '6' => __('6 Columns'), '7' => __('7 Columns'), '8' => __('8 Columns')];
    }
}
