<?php

namespace ModernRetail\Import\Plugin;

class FlatState
{

    public function __construct(    \ModernRetail\Import\Helper\Data $dataHelper)
    {
        $this->helper = $dataHelper;

    }
    /**
     * Check if Flat Index is enabled
     *
     * @return bool
     */
    public function aroundIsFlatEnabled($subject, $proceed)
    {
        $rootValue = $proceed();
        if ($rootValue===false) return $rootValue;
        if ($this->helper->isFlatDataEnabled()===true) return true;
        return false;
    }

}