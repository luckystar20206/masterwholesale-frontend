<?php
namespace Smartwave\Megamenu\Model\Attribute;

class Width extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => '0', 'label' => __('Do not show')],
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
                ['value' => '11', 'label' => __('11/12')],
                ['value' => '12', 'label' => __('12/12')]
            ];
        }
        
        return $this->_options;
    }
}