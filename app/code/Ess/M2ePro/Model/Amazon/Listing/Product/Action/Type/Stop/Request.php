<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Stop;

/**
 * Class Request
 * @package Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Stop
 */
class Request extends \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Request
{
    //########################################

    protected function getActionData()
    {
        return [
            'sku' => $this->getAmazonListingProduct()->getSku(),
            'qty' => 0
        ];
    }

    //########################################
}
