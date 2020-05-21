<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Block\System\Contact\Header;

class Text extends \Magento\Backend\Block\Template
{
    const STORE_URL = 'https://store.neklo.com/';
    const STORE_LABEL = 'store.neklo.com';

    public function getStoreUrl()
    {
        return self::STORE_URL;
    }

    public function getStoreLabel()
    {
        return self::STORE_LABEL;
    }
}