<?php
/**
 * Copyright © 2015 Mwi. All rights reserved.
 */
namespace Mwi\StoreTiming\Model\ResourceModel;

/**
 * Timing resource
 */
class Timing extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('storetiming_timing', 'id');
    }

  
}
