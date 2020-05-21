<?php
namespace Smartwave\Porto\Model\Config\Design\Font;

class Size implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Default')], 
            ['value' => '10px', 'label' => __('10px')], 
            ['value' => '11px', 'label' => __('11px')], 
            ['value' => '12px', 'label' => __('12px')], 
            ['value' => '13px', 'label' => __('13px')], 
            ['value' => '14px', 'label' => __('14px')], 
            ['value' => '15px', 'label' => __('15px')], 
            ['value' => '16px', 'label' => __('16px')], 
            ['value' => '17px', 'label' => __('17px')], 
            ['value' => '18px', 'label' => __('18px')], 
            ['value' => '19px', 'label' => __('19px')], 
            ['value' => '20px', 'label' => __('20px')]
        ];
    }

    public function toArray()
    {
        return [
            '' => __('Default'), 
            '10px' => __('10px'), 
            '11px' => __('11px'), 
            '12px' => __('12px'), 
            '13px' => __('13px'), 
            '14px' => __('14px'), 
            '15px' => __('15px'), 
            '16px' => __('16px'), 
            '17px' => __('17px'), 
            '18px' => __('18px'), 
            '19px' => __('19px'), 
            '20px' => __('20px')
        ];
    }
}
