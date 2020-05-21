<?php

namespace WeltPixel\GoogleCards\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    protected $_cardsOptions;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        
        $this->_cardsOptions = $this->scopeConfig->getValue('weltpixel_google_cards', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getDescriptionType($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/general/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['general']['description'];
        }
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getBrand($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/general/brand', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['general']['brand'];
        }
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getSku($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/general/sku', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['general']['sku'];
        }
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getTwitterCardDescriptionType($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/twitter_cards/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['twitter_cards']['description'];
        }
    }


    /**
     * @param int $storeId
     * @return string
     */
    public function getTwitterCardType($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/twitter_cards/card_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['twitter_cards']['card_type'];
        }
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getTwitterCreator($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/twitter_cards/creator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['twitter_cards']['creator'];
        }
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getTwitterSite($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/twitter_cards/site', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['twitter_cards']['site'];
        }
    }


    /**
     * @return string
     */
    public function getTwitterShippingCountry() {
        return $this->scopeConfig->getValue('shipping/origin/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @param int $storeId
     * @return string
     */
    public function getFacebookDescriptionType($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/facebook_opengraph/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['facebook_opengraph']['description'];
        }
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getFacebookSiteName($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/facebook_opengraph/site_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['facebook_opengraph']['site_name'];
        }
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getFacebookAppId($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/facebook_opengraph/app_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['facebook_opengraph']['app_id'];
        }
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getFacebookRetailerId($storeId = 0)
    {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/facebook_opengraph/retailer_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['facebook_opengraph']['retailer_id'];
        }
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getGoogleCardsPrice($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/general/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['general']['price'];
        }
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getTwitterCardsPrice($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/twitter_cards/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['twitter_cards']['price'];
        }
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getFacebookOpenGraphPrice($storeId = 0) {
        if ($storeId) {
            return $this->scopeConfig->getValue('weltpixel_google_cards/facebook_opengraph/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->_cardsOptions['facebook_opengraph']['price'];
        }
    }
}
