<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Model\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    const NEW_REQUEST_STATUS        = 1;
    const PENDING_REQUEST_STATUS    = 2;
    const ACCEPTED_REQUEST_STATUS   = 3;
    const COMPLETED_REQUEST_STATUS  = 4;
    const DECLINED_REQUEST_STATUS   = 5;
    const EXPIRED_REQUEST_STATUS    = 6;

    /**
     * Retrieve status options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $array = $this->getArray();
        $options = [];
        foreach ($array as $value => $label) {
            $options[] = ['value' => $value, 'label' => __($label)];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return [
            self::NEW_REQUEST_STATUS           => 'New',
            self::PENDING_REQUEST_STATUS       => 'Pending',
            self::ACCEPTED_REQUEST_STATUS      => 'Accepted',
            self::COMPLETED_REQUEST_STATUS     => 'Completed',
            self::DECLINED_REQUEST_STATUS      => 'Declined',
            self::EXPIRED_REQUEST_STATUS       => 'Expired',
        ];
    }
}
