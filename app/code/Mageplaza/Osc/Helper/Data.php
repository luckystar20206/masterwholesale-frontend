<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Helper;

use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Observer\IsAllowedGuestCheckoutObserver;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Osc\Model\System\Config\Source\ComponentPosition;
use Zend_Serializer_Exception;

/**
 * Class Data
 * @package Mageplaza\Osc\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH    = 'osc';
    const CONFIG_PATH_DISPLAY   = 'display_configuration';
    const CONFIG_PATH_DESIGN    = 'design_configuration';
    const CONFIG_PATH_BLOCK     = 'block_configuration';
    const CONFIG_PATH_FIELD     = 'field_configuration';
    const SORTED_FIELD_POSITION = 'osc/field/position';
    const OA_FIELD_POSITION     = 'osc/oa_field/position';
    const CONFIG_ROUTE_PATH     = 'onestepcheckout';

    const UTM_PARAMS        = '?utm_source=configuration&utm_medium=link&utm_campaign=one-step-checkout';
    const CUSTOMER_ATTR_URL = 'https://www.mageplaza.com/magento-2-customer-attributes/' . self::UTM_PARAMS;
    const ORDER_ATTR_URL    = 'https://www.mageplaza.com/magento-2-order-attributes/' . self::UTM_PARAMS;

    /**
     * @var bool Osc Method Register
     */
    protected $_flagOscMethodRegister = false;

    /**
     * @var Address
     */
    protected $_addressHelper;

    /**
     * @return Address
     */
    public function getAddressHelper()
    {
        if (!$this->_addressHelper) {
            $this->_addressHelper = $this->objectManager->get(Address::class);
        }

        return $this->_addressHelper;
    }

    /**
     * Check the current page is osc
     *
     * @param null $store
     *
     * @return bool
     */
    public function isOscPage($store = null)
    {
        $moduleEnable = $this->isEnabled($store);

        return $moduleEnable && ($this->_request->getRouteName() === self::CONFIG_ROUTE_PATH);
    }

    /**
     * @return bool
     */
    public function isFlagOscMethodRegister()
    {
        return $this->_flagOscMethodRegister;
    }

    /**
     * @param bool $flag
     */
    public function setFlagOscMethodRegister($flag)
    {
        $this->_flagOscMethodRegister = $flag;
    }

    /**
     * One step checkout page title
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getCheckoutTitle($store = null)
    {
        return $this->getConfigGeneral('title', $store) ?: 'One Step Checkout';
    }

    /************************ General Configuration *************************/
    /**
     * One step checkout page description
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getCheckoutDescription($store = null)
    {
        return $this->getConfigGeneral('description', $store);
    }

    /**
     * Get magento default country
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getDefaultCountryId($store = null)
    {
        return $this->objectManager->get(\Magento\Directory\Helper\Data::class)->getDefaultCountry($store);
    }

    /**
     * Default shipping method
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getDefaultShippingMethod($store = null)
    {
        return $this->getConfigGeneral('default_shipping_method', $store);
    }

    /**
     * Default payment method
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getDefaultPaymentMethod($store = null)
    {
        return $this->getConfigGeneral('default_payment_method', $store);
    }

    /**
     * Allow guest checkout
     *
     * @param Quote $quote
     * @param null $store
     *
     * @return bool|mixed
     */
    public function getAllowGuestCheckout($quote, $store = null)
    {
        $allowGuestCheckout = $this->getConfigGeneral('allow_guest_checkout', $store);

        $flag = $this->scopeConfig->isSetFlag(
            IsAllowedGuestCheckoutObserver::XML_PATH_DISABLE_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($flag) {
            /** @var Item $item */
            foreach ($quote->getAllItems() as $item) {
                if (($product = $item->getProduct()) && $product->getTypeId() === Type::TYPE_DOWNLOADABLE) {
                    return false;
                }
            }
        }

        return $allowGuestCheckout;
    }

    /**
     * Redirect To OneStepCheckout
     *
     * @param null $store
     *
     * @return bool
     */
    public function isRedirectToOneStepCheckout($store = null)
    {
        return (bool) $this->getConfigGeneral('redirect_to_one_step_checkout', $store);
    }

    /**
     * Show billing address
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getShowBillingAddress($store = null)
    {
        return (bool) $this->getConfigGeneral('show_billing_address', $store);
    }

    /**
     * Google api key
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getGoogleApiKey($store = null)
    {
        return $this->getConfigGeneral('google_api_key', $store);
    }

    /**
     * Google restric country
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getGoogleSpecificCountry($store = null)
    {
        return $this->getConfigGeneral('google_specific_country', $store);
    }

    /**
     * Check if the page is https
     *
     * @return bool
     */
    public function isGoogleHttps()
    {
        return $this->getAutoDetectedAddress() === 'google' && $this->_request->isSecure();
    }

    /**
     * Get auto detected address
     *
     * @param null $store
     *
     * @return null|'google'|'pca'
     */
    public function getAutoDetectedAddress($store = null)
    {
        return $this->getConfigGeneral('auto_detect_address', $store);
    }

    /**
     * Login link will be hide if this function return true
     *
     * @param null $store
     *
     * @return bool
     */
    public function isDisableAuthentication($store = null)
    {
        return !$this->getDisplayConfig('is_enabled_login_link', $store);
    }

    /********************************** Display Configuration *********************
     *
     * @param $code
     * @param null $store
     *
     * @return mixed
     */
    public function getDisplayConfig($code = '', $store = null)
    {
        $code = $code ? self::CONFIG_PATH_DISPLAY . '/' . $code : self::CONFIG_PATH_DISPLAY;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * Item detail will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return bool
     */
    public function isDisabledReviewCartSection($store = null)
    {
        return !$this->getDisplayConfig('is_enabled_review_cart_section', $store);
    }

    /**
     * Item list toggle will be shown if this function return 'true'
     *
     * @param null $store
     *
     * @return bool
     */
    public function isShowItemListToggle($store = null)
    {
        return (bool) $this->getDisplayConfig('is_show_item_list_toggle', $store);
    }

    /**
     * Product image will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return bool
     */
    public function isHideProductImage($store = null)
    {
        return !$this->getDisplayConfig('is_show_product_image', $store);
    }

    /**
     * Coupon will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return mixed
     */
    public function disabledPaymentCoupon($store = null)
    {
        return (int) $this->getDisplayConfig('show_coupon', $store) !== ComponentPosition::SHOW_IN_PAYMENT;
    }

    /**
     * Coupon will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return mixed
     */
    public function disabledReviewCoupon($store = null)
    {
        return (int) $this->getDisplayConfig('show_coupon', $store) !== ComponentPosition::SHOW_IN_REVIEW;
    }

    /**
     * Comment will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return mixed
     */
    public function isDisabledComment($store = null)
    {
        return !$this->getDisplayConfig('is_enabled_comments', $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function getShowTOC($store = null)
    {
        return $this->getDisplayConfig('show_toc', $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function isEnabledTOC($store = null)
    {
        return (int) $this->getDisplayConfig('show_toc', $store) !== ComponentPosition::NOT_SHOW;
    }

    /**
     * Term and condition checkbox in payment block will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return mixed
     */
    public function disabledPaymentTOC($store = null)
    {
        return (int) $this->getDisplayConfig('show_toc', $store) !== ComponentPosition::SHOW_IN_PAYMENT;
    }

    /**
     * Term and condition checkbox in review will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return mixed
     */
    public function disabledReviewTOC($store = null)
    {
        return (int) $this->getDisplayConfig('show_toc', $store) !== ComponentPosition::SHOW_IN_REVIEW;
    }

    /**
     * GiftMessage will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return mixed
     */
    public function isDisabledGiftMessage($store = null)
    {
        return !$this->getDisplayConfig('is_enabled_gift_message', $store);
    }

    /**
     * Gift message items
     *
     * @param null $store
     *
     * @return bool
     */
    public function isEnableGiftMessageItems($store = null)
    {
        return (bool) $this->getDisplayConfig('is_enabled_gift_message_items', $store);
    }

    /**
     * Gift wrap block will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return mixed
     */
    public function isDisabledGiftWrap($store = null)
    {
        $giftWrapEnabled = $this->getDisplayConfig('is_enabled_gift_wrap', $store);
        $giftWrapAmount  = $this->getOrderGiftwrapAmount();

        return !$giftWrapEnabled || ($giftWrapAmount < 0);
    }

    /**
     * Gift wrap amount
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getOrderGiftWrapAmount($store = null)
    {
        return (float) $this->getDisplayConfig('gift_wrap_amount', $store);
    }

    /**
     * @return array
     */
    public function getGiftWrapConfiguration()
    {
        return [
            'gift_wrap_type'   => $this->getGiftWrapType(),
            'gift_wrap_amount' => $this->formatGiftWrapAmount()
        ];
    }

    /**
     * Gift wrap type
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getGiftWrapType($store = null)
    {
        return $this->getDisplayConfig('gift_wrap_type', $store);
    }

    /**
     * @return mixed
     */
    public function formatGiftWrapAmount()
    {
        $giftWrapAmount = $this->objectManager->get(\Magento\Checkout\Helper\Data::class)
            ->formatPrice($this->getOrderGiftWrapAmount());

        return $giftWrapAmount;
    }

    /**
     * Newsleter block will be hided if this function return 'true'
     *
     * @param null $store
     *
     * @return mixed
     */
    public function isDisabledNewsletter($store = null)
    {
        return !$this->getDisplayConfig('is_enabled_newsletter', $store);
    }

    /**
     * Is newsleter subcribed default
     *
     * @param null $store
     *
     * @return mixed
     */
    public function isSubscribedByDefault($store = null)
    {
        return (bool) $this->getDisplayConfig('is_checked_newsletter', $store);
    }

    /**
     * Social Login On Checkout Page
     *
     * @param null $store
     *
     * @return bool
     */
    public function isDisabledSocialLoginOnCheckout($store = null)
    {
        return !$this->getDisplayConfig('is_enabled_social_login', $store);
    }

    /**
     * Survey
     *
     * @param null $store
     *
     * @return bool
     */
    public function isDisableSurvey($store = null)
    {
        return !$this->getDisplayConfig('is_enabled_survey', $store);
    }

    /**
     * Survey Question
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getSurveyQuestion($store = null)
    {
        return $this->getDisplayConfig('survey_question', $store);
    }

    /**
     * @param null $stores
     *
     * @return array
     * @throws Zend_Serializer_Exception
     */
    public function getSurveyAnswers($stores = null)
    {
        return $this->unserialize($this->getDisplayConfig('survey_answers', $stores));
    }

    /**
     * Allow Customer Add Other Option
     *
     * @param null $stores
     *
     * @return mixed
     */
    public function isAllowCustomerAddOtherOption($stores = null)
    {
        return $this->getDisplayConfig('allow_customer_add_other_option', $stores);
    }

    /**
     * @param null $stores
     *
     * @return int
     */
    public function isEnabledSealBlock($stores = null)
    {
        return (int) $this->getDisplayConfig('seal_block/is_enabled_seal_block', $stores);
    }

    /**
     * @param null $stores
     *
     * @return mixed
     */
    public function getSealStaticBlock($stores = null)
    {
        return $this->getDisplayConfig('seal_block/seal_static_block', $stores);
    }

    /**
     * @param null $stores
     *
     * @return mixed
     */
    public function getSealImage($stores = null)
    {
        return $this->getDisplayConfig('seal_block/seal_image', $stores);
    }

    /**
     * @param null $stores
     *
     * @return mixed
     */
    public function getSealDescription($stores = null)
    {
        return $this->getDisplayConfig('seal_block/seal_description', $stores);
    }

    /**
     * Get layout tempate: 1 or 2 or 3 columns
     *
     * @param null $store
     *
     * @return string
     */
    public function getLayoutTemplate($store = null)
    {
        return 'Mageplaza_Osc/' . $this->getDesignConfig('page_layout', $store);
    }

    /***************************** Design Configuration *****************************
     *
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getDesignConfig($code = '', $store = null)
    {
        $code = $code ? self::CONFIG_PATH_DESIGN . '/' . $code : self::CONFIG_PATH_DESIGN;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @return bool
     */
    public function isUsedMaterialDesign()
    {
        return $this->getDesignConfig('page_design') === 'material';
    }

    /***************************** CMS Static Block Configuration *****************************
     *
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getStaticBlockConfig($code = '', $store = null)
    {
        $code = $code ? self::CONFIG_PATH_BLOCK . '/' . $code : self::CONFIG_PATH_BLOCK;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isEnableStaticBlock($store = null)
    {
        return (bool) $this->getStaticBlockConfig('is_enabled_block', $store);
    }

    /**
     * @param null $stores
     *
     * @return mixed
     * @throws Zend_Serializer_Exception
     */
    public function getStaticBlockList($stores = null)
    {
        return $this->unserialize($this->getStaticBlockConfig('list', $stores));
    }

    /***************************** Custom Fields Configuration *****************************
     *
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getCustomFieldConfig($code = '', $store = null)
    {
        $code = $code ? self::CONFIG_PATH_FIELD . '/' . $code : self::CONFIG_PATH_FIELD;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @param int $key
     * @param null $store
     *
     * @return string
     */
    public function getCustomFieldLabel($key = 1, $store = null)
    {
        return $this->getCustomFieldConfig('field_' . $key . '_label', $store);
    }

    /***************************** Compatible Modules *****************************
     *
     * @return bool
     */
    public function isEnabledMultiSafepay()
    {
        return $this->_moduleManager->isOutputEnabled('MultiSafepay_Connect');
    }

    /**
     * @return bool
     */
    public function isEnableModulePostNL()
    {
        return $this->isModuleOutputEnabled('TIG_PostNL');
    }

    /**
     * @return bool
     */
    public function isEnableAmazonPay()
    {
        return $this->isModuleOutputEnabled('Amazon_Payment');
    }

    /**
     * @return bool
     */
    public function isEnableCustomerAttributes()
    {
        return $this->isModuleOutputEnabled('Mageplaza_CustomerAttributes');
    }

    /**
     * @return bool
     */
    public function isEnableOrderAttributes()
    {
        return $this->isModuleOutputEnabled('Mageplaza_OrderAttributes');
    }

    /**
     * Get current theme id
     * @return mixed
     */
    public function getCurrentThemeId()
    {
        return $this->getConfigValue(DesignInterface::XML_PATH_THEME_ID);
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isShowHeaderFooter($store = null)
    {
        return $this->getDisplayConfig('is_display_foothead', $store);
    }

    /**
     * @return mixed
     */
    public function checkVersion()
    {
        return $this->versionCompare('2.3.1', '<');
    }

    /**
     * @param null $store
     *
     * @return mixed|string
     */
    public function getOscRoute($store = null)
    {
        $route = $this->getConfigGeneral('route', $store);

        return !empty($route) ? $route : self::CONFIG_ROUTE_PATH;
    }

    /**
     * @param $string
     *
     * @return mixed
     * @throws Zend_Serializer_Exception
     */
    public function unserialize($string)
    {
        if ($string) {
            return parent::unserialize($string);
        }

        return [];
    }

    /**
     * Override the Core version compare method
     *
     * @param $ver
     * @param string $operator
     *
     * @return mixed
     */
    public function versionCompare($ver, $operator = '>=')
    {
        $productMetadata = $this->objectManager->get(ProductMetadataInterface::class);
        $version         = $productMetadata->getVersion(); //will return the magento version

        return version_compare($version, $ver, $operator);
    }
}
