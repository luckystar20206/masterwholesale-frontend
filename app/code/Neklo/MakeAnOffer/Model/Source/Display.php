<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Model\Source;

class Display implements \Magento\Framework\Option\ArrayInterface
{
    const OUTPUT_TYPE_BLOCK = 0;
    const OUTPUT_TYPE_POPUP = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OUTPUT_TYPE_BLOCK, 'label' => __('Block')],
            ['value' => self::OUTPUT_TYPE_POPUP, 'label' => __('Popup')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Popup'), 1 => __('Block')];
    }
}
