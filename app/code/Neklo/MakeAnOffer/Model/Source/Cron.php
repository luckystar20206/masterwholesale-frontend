<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Model\Source;

class Cron implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $options = [
            ['value' => '1', 'label' => __('1 Day')],
            ['value' => '7', 'label' => __('1 Week')],
            ['value' => '15', 'label' => __('15 Days')],
            ['value' => '30', 'label' => __('30 Days')],
        ];

        return $options;
    }
}
