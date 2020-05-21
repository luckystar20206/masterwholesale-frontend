<?php
namespace Smartwave\Porto\Model\Config\Settings\Product;

class Tabstyle implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Horizontal')], 
            ['value' => 'vertical', 'label' => __('Vertical')], 
            ['value' => 'accordion', 'label' => __('Accordion')], 
            ['value' => 'sticky', 'label' => __('Sticky Tab')]
        ];
    }

    public function toArray()
    {
        return [
            '' => __('Horizontal'), 
            'vertical' => __('Vertical'), 
            'accordion' => __('Accordion'), 
            'sticky' => __('Sticky Tab')
        ];
    }
}
