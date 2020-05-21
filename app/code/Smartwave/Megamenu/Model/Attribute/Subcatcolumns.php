<?php
namespace Smartwave\Megamenu\Model\Attribute;

class Subcatcolumns extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => '', 'label' => __('Default')],
                ['value' => '1', 'label' => __('1 Column')],
                ['value' => '2', 'label' => __('2 Columns')],
                ['value' => '3', 'label' => __('3 Columns')],
                ['value' => '4', 'label' => __('4 Columns')],
                ['value' => '5', 'label' => __('5 Columns')],
                ['value' => '6', 'label' => __('6 Columns')],
                ['value' => '7', 'label' => __('7 Columns')],
                ['value' => '8', 'label' => __('8 Columns')],
                ['value' => '9', 'label' => __('9 Columns')],
                ['value' => '10', 'label' => __('10 Columns')],
                ['value' => '11', 'label' => __('11 Columns')],
                ['value' => '12', 'label' => __('12 Columns')]
            ];
        }
        
        return $this->_options;
    }
}