<?php

namespace ModernRetail\ApiOrders\Ui\Component\Listing\Column\Options;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PrebookStatus
 */
class MrSentToApi implements OptionSourceInterface
{

    const PRE_BOOK_YES=1;
    const PRE_BOOK_NO=0;

    public static function getOptionArray()
    {
        return [
            "complete" => __('Yes'),
            "no" => __('No')
        ];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
}