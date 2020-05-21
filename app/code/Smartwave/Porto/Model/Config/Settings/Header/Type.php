<?php
namespace Smartwave\Porto\Model\Config\Settings\Header;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Type 1')], 
            ['value' => '2', 'label' => __('Type 2')], 
            ['value' => '3', 'label' => __('Type 3')], 
            ['value' => '4', 'label' => __('Type 4')], 
            ['value' => '5', 'label' => __('Type 5')], 
            ['value' => '6', 'label' => __('Type 6')], 
            ['value' => '7', 'label' => __('Type 7')], 
            ['value' => '8', 'label' => __('Type 8')], 
            ['value' => '9', 'label' => __('Type 9')], 
            ['value' => '10', 'label' => __('Type 10')], 
            ['value' => '11', 'label' => __('Type 11')], 
            ['value' => '12', 'label' => __('Type 12')], 
            ['value' => '13', 'label' => __('Type 13')], 
            ['value' => '14', 'label' => __('Type 14')],
            ['value' => '15', 'label' => __('Type 15')],
            ['value' => '16', 'label' => __('Type 16')],
            ['value' => '17', 'label' => __('Type 17')],
            ['value' => '18', 'label' => __('Type 18')],
            ['value' => '19', 'label' => __('Type 19')],
            ['value' => '20', 'label' => __('Type 20')]
        ];
    }

    public function toArray()
    {
        return [
            '1' => __('Type 1'), 
            '2' => __('Type 2'), 
            '3' => __('Type 3'), 
            '4' => __('Type 4'), 
            '5' => __('Type 5'), 
            '6' => __('Type 6'), 
            '7' => __('Type 7'), 
            '8' => __('Type 8'), 
            '9' => __('Type 9'), 
            '10' => __('Type 10'), 
            '11' => __('Type 11'), 
            '12' => __('Type 12'), 
            '13' => __('Type 13'), 
            '14' => __('Type 14'),
            '15' => __('Type 15'),
            '16' => __('Type 16'),
            '17' => __('Type 17'),
            '18' => __('Type 18'),
            '19' => __('Type 19'),
            '20' => __('Type 20')
        ];
    }
}
