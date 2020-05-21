<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


// @codingStandardsIgnoreFile

namespace Neklo\MakeAnOffer\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Statistic extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'neklo_make_an_offer_statistic';

    protected $_cacheTag = 'neklo_make_an_offer_statistic';

    protected $_eventPrefix = 'neklo_make_an_offer_statistic';

    protected function _construct()
    {
        $this->_init(\Neklo\MakeAnOffer\Model\ResourceModel\Statistic::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
