<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Helper;

class Serializer extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @param $data
     * @return bool|array
     */
    public function unserialize($data)
    {
        if (false === $data || null === $data || '' === $data || '[]' === $data || 'a:0:{}' === $data) {
            return false;
        }

        return json_decode($data, true);
    }

    /**
     * @param $data
     * @return string
     */
    public function serialize($data)
    {
        return json_encode($data);
    }
}
