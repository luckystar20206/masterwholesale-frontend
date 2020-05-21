<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


// @codingStandardsIgnoreFile

namespace Neklo\MakeAnOffer\Model\ResourceModel;

class Statistic extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('neklo_make_an_offer_statistic', 'id');
    }
}
