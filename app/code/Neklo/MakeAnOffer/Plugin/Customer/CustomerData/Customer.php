<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Plugin\Customer\CustomerData;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\CustomerData\Customer as CustomerData;

class Customer
{
    private $currentCustomer;

    public function __construct(CurrentCustomer $currentCustomer)
    {
        $this->currentCustomer = $currentCustomer;
    }

    public function afterGetSectionData(
        CustomerData $subject,
        array $result
    ) {
        if (!$this->currentCustomer->getCustomerId()) {
            return $result;
        }

        $customerEmail = $this->currentCustomer->getCustomer()->getEmail();
        $result['customerEmail'] = $customerEmail;
        
        return $result;
    }
}
