<?php

namespace WeltPixel\GoogleCards\Model\Pricing\Render;

/**
 * Class Amount
 * @package WeltPixel\GoogleCards\Model\Pricing\Render
 */
class Amount extends \Magento\Framework\Pricing\Render\Amount
{
    public function getSchema()
    {
        if ($this->_scopeConfig->getValue('weltpixel_google_cards/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
        {
            return false;
        }

        return parent::getSchema();
    }
}