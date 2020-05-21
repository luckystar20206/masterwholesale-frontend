<?php

namespace ModernRetail\ApiOrders\Ui\Component\Listing\Column\Options;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PrebookStatus
 */
class MrApiSyncStatus implements OptionSourceInterface
{
    public static function getOptionArray()
    {
        return [
            'complete' => __('Yes'),
            'scheduled' => __('Scheduled'),
            'pending' => __('Pending'),
            'failed' => __('Failed'),
            'no' => __('No')
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