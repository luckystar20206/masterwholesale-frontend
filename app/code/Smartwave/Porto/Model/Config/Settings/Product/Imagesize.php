<?php
namespace Smartwave\Porto\Model\Config\Settings\Product;

class Imagesize implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('1/12')], 
            ['value' => '2', 'label' => __('2/12')], 
            ['value' => '3', 'label' => __('3/12')], 
            ['value' => '4', 'label' => __('4/12')], 
            ['value' => '5', 'label' => __('5/12')], 
            ['value' => '6', 'label' => __('6/12')], 
            ['value' => '7', 'label' => __('7/12')], 
            ['value' => '8', 'label' => __('8/12')], 
            ['value' => '9', 'label' => __('9/12')], 
            ['value' => '10', 'label' => __('10/12')], 
            ['value' => '11', 'label' => __('11/12')]
        ];
    }

    public function toArray()
    {
        return [
            '1' => __('1/12'), 
            '2' => __('2/12'), 
            '3' => __('3/12'), 
            '4' => __('4/12'), 
            '5' => __('5/12'), 
            '6' => __('6/12'), 
            '7' => __('7/12'), 
            '8' => __('8/12'), 
            '9' => __('9/12'), 
            '10' => __('10/12'), 
            '11' => __('11/12')
        ];
    }
}
