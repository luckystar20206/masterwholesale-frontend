<?php

namespace ModernRetail\Import\Plugin;

class ProductCollection
{

    public function __construct(    \ModernRetail\Import\Helper\Data $dataHelper)
    {
        $this->helper = $dataHelper;

    }

    public function aroundIsEnabledFlat($subject, $proceed)
    {
        $rootValue = $proceed();
   

        if ($rootValue===false) return $rootValue;

        if ($this->helper->isFlatDataEnabled()===true) return true;

        return false;

    }


}