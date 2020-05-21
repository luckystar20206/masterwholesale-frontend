<?php
namespace Smartwave\Porto\Model\Config\Settings\Installation;

class Demoversion implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('All')], 
            ['value' => 'demo01', 'label' => __('Demo 1')], 
            ['value' => 'demo02', 'label' => __('Demo 2')], 
            ['value' => 'demo03', 'label' => __('Demo 3')], 
            ['value' => 'demo04', 'label' => __('Demo 4')], 
            ['value' => 'demo05', 'label' => __('Demo 5')], 
            ['value' => 'demo06', 'label' => __('Demo 6')], 
            ['value' => 'demo07', 'label' => __('Demo 7')], 
            ['value' => 'demo08', 'label' => __('Demo 8')], 
            ['value' => 'demo09', 'label' => __('Demo 9')], 
            ['value' => 'demo10', 'label' => __('Demo 10')], 
            ['value' => 'demo11', 'label' => __('Demo 11')], 
            ['value' => 'demo12', 'label' => __('Demo 12')], 
            ['value' => 'demo13', 'label' => __('Demo 13')], 
            ['value' => 'demo14', 'label' => __('Demo 14')], 
            ['value' => 'demo15', 'label' => __('Demo 15')], 
            ['value' => 'demo16', 'label' => __('Demo 16')], 
            ['value' => 'demo17', 'label' => __('Demo 17')], 
            ['value' => 'demo18', 'label' => __('Demo 18')], 
            ['value' => 'demo19', 'label' => __('Demo 19')], 
            ['value' => 'demo20', 'label' => __('Demo 20')],
            ['value' => 'demo21', 'label' => __('Demo 21')]
        ];
    }

    public function toArray()
    {
        return [
            '0' => __('All'), 
            'demo01' => __('Demo 1'), 
            'demo02' => __('Demo 2'), 
            'demo03' => __('Demo 3'), 
            'demo04' => __('Demo 4'), 
            'demo05' => __('Demo 5'), 
            'demo06' => __('Demo 6'), 
            'demo07' => __('Demo 7'), 
            'demo08' => __('Demo 8'), 
            'demo09' => __('Demo 9'), 
            'demo10' => __('Demo 10'), 
            'demo11' => __('Demo 11'), 
            'demo12' => __('Demo 12'), 
            'demo13' => __('Demo 13'), 
            'demo14' => __('Demo 14'), 
            'demo15' => __('Demo 15'), 
            'demo16' => __('Demo 16'), 
            'demo17' => __('Demo 17'), 
            'demo18' => __('Demo 18'), 
            'demo19' => __('Demo 19'), 
            'demo20' => __('Demo 20'),
            'demo21' => __('Demo 21')
        ];
    }
}
