<?php
namespace Smartwave\Porto\Model\Config\Design\Font;

class Family implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'custom', 'label' => __('Custom...')], 
            ['value' => 'google', 'label' => __('Google Fonts...')], 
            ['value' => '"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif', 'label' => __('"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif')], 
            ['value' => 'Arial, "Helvetica Neue", Helvetica, sans-serif', 'label' => __('Arial, "Helvetica Neue", Helvetica, sans-serif')], 
            ['value' => 'Georgia, serif', 'label' => __('Georgia, serif')], 
            ['value' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif', 'label' => __('"Lucida Sans Unicode", "Lucida Grande", sans-serif')], 
            ['value' => '"Palatino Linotype", "Book Antiqua", Palatino, serif', 'label' => __('"Palatino Linotype", "Book Antiqua", Palatino, serif')], 
            ['value' => 'Tahoma, Geneva, sans-serif', 'label' => __('Tahoma, Geneva, sans-serif')], 
            ['value' => '"Trebuchet MS", Helvetica, sans-serif', 'label' => __('"Trebuchet MS", Helvetica, sans-serif')], 
            ['value' => 'Verdana, Geneva, sans-serif', 'label' => __('Verdana, Geneva, sans-serif')], 
            ['value' => '19px', 'label' => __('19px')], 
            ['value' => '20px', 'label' => __('20px')]
        ];
    }

    public function toArray()
    {
        return [
            'custom' => __('Custom...'), 
            'google' => __('Google Fonts...'), 
            '"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif' => __('"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif'), 
            'Arial, "Helvetica Neue", Helvetica, sans-serif' => __('Arial, "Helvetica Neue", Helvetica, sans-serif'), 
            'Georgia, serif' => __('Georgia, serif'), 
            '"Lucida Sans Unicode", "Lucida Grande", sans-serif' => __('"Lucida Sans Unicode", "Lucida Grande", sans-serif'), 
            '"Palatino Linotype", "Book Antiqua", Palatino, serif' => __('"Palatino Linotype", "Book Antiqua", Palatino, serif'), 
            'Tahoma, Geneva, sans-serif' => __('Tahoma, Geneva, sans-serif'), 
            '"Trebuchet MS", Helvetica, sans-serif' => __('"Trebuchet MS", Helvetica, sans-serif'), 
            'Verdana, Geneva, sans-serif' => __('Verdana, Geneva, sans-serif')
        ];
    }
}
