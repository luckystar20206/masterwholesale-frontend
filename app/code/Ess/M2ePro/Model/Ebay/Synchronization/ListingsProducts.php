<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Synchronization;

/**
 * Class ListingsProducts
 * @package Ess\M2ePro\Model\Ebay\Synchronization
 */
class ListingsProducts extends \Ess\M2ePro\Model\Ebay\Synchronization\AbstractModel
{
    //########################################

    /**
     * @return string
     */
    protected function getType()
    {
        return \Ess\M2ePro\Model\Synchronization\Task\AbstractComponent::LISTINGS_PRODUCTS;
    }

    /**
     * @return null
     */
    protected function getNick()
    {
        return null;
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Listings Products';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('ListingsProducts\RemoveDuplicates') ? false : $result;
        $result = !$this->processTask('ListingsProducts\Update') ? false : $result;

        return $result;
    }

    //########################################
}
