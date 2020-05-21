<?php
/**
 * @category    Ubertheme
 * @package     Ubertheme_UbDatamigration
 * @author      Ubertheme.com
 * @copyright   Copyright 2009-2016 Ubertheme
 */

namespace Ubertheme\Ubdatamigration\Model\Indexer\Product\Category\Action;


class Full extends \Magento\Catalog\Model\Indexer\Category\Product\Action\Full
{
    protected function isRangingNeeded()
    {
        return false;
    }

}
