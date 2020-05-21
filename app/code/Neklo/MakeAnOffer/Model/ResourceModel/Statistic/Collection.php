<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


// @codingStandardsIgnoreFile

namespace Neklo\MakeAnOffer\Model\ResourceModel\Statistic;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected $_eventPrefix = 'neklo_make_an_offer_statistic_collection';

    protected $_eventObject = 'statistic_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Neklo\MakeAnOffer\Model\Statistic::class,
            \Neklo\MakeAnOffer\Model\ResourceModel\Statistic::class
        );
    }
}
