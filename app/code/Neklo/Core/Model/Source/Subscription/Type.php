<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Model\Source\Subscription;

class Type implements \Magento\Framework\Option\ArrayInterface
{

    const UPDATE_CODE = 'UPDATE';
    const UPDATE_LABEL = 'My extensions updates';

    const RELEASE_CODE = 'RELEASE';
    const RELEASE_LABEL = 'New Releases';

    const UPDATE_ALL_CODE = 'UPDATE_ALL';
    const UPDATE_ALL_LABEL = 'All extensions updates';

    const PROMO_CODE = 'PROMO';
    const PROMO_LABEL = 'Promotions / Discounts';

    const INFO_CODE = 'INFO';
    const INFO_LABEL = 'Other information';

    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::UPDATE_CODE,
                'label' => __('%1', self::UPDATE_LABEL),
            ],
            [
                'value' => self::RELEASE_CODE,
                'label' => __('%1', self::RELEASE_LABEL),
            ],
            [
                'value' => self::UPDATE_ALL_CODE,
                'label' => __('%1', self::UPDATE_ALL_LABEL),
            ],
            [
                'value' => self::PROMO_CODE,
                'label' => __('%1', self::PROMO_LABEL),
            ],
            [
                'value' => self::INFO_CODE,
                'label' => __('%1', self::INFO_LABEL),
            ],
        ];
        return $options;
    }
}
