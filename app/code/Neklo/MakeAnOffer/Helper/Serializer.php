<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Serializer extends AbstractHelper
{
    public function unserialize($data)
    {
        if (false === $data || null === $data || '' === $data || '[]' === $data || 'a:0:{}' === $data) {
            return false;
        }

        $emails = json_decode($data, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $emails;
        } else {
            $emails = unserialize($data); // @codingStandardsIgnoreLine

            return $emails;
        }
    }
}
