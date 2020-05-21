<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const GENERAL_ENABLE = 'neklo_makeanoffer/general/enable';

    const COUPON_LIFETIME = 'neklo_makeanoffer/coupon/coupon_lifetime';

    const COUPON_PREFIX = 'neklo_makeanoffer/coupon/coupon_prefix';

    const COUPON_SUFFIX = 'neklo_makeanoffer/coupon/coupon_suffix';

    const COUPON_DASH = 'neklo_makeanoffer/coupon/dash';

    const COUPON_LENGTH = 'neklo_makeanoffer/coupon/coupon_length';

    const FRONTEND_DISPLAY = 'neklo_makeanoffer/frontend/display';

    const FRONTEND_DISPLAY_PRICE_TAX = 'tax/display/type';

    const FRONTEND_DISPLAY_ANOTHER_STORE_LINK = 'neklo_makeanoffer/frontend/display_another_store_link';

    const FRONTEND_BUTTON_LABEL = 'neklo_makeanoffer/frontend/button_label';

    const FRONTEND_DESCRIPTION = 'neklo_makeanoffer/frontend/short_descriptions';

    const FRONTEND_TITLE = 'neklo_makeanoffer/frontend/title';

    const FRONTEND_SUCCESS_MESSAGE = 'neklo_makeanoffer/frontend/success_message';

    const EMAIL_DECLINE_TEMPLATE = 'neklo_makeanoffer/email/decline_template';

    const EMAIL_ACCEPT_TEMPLATE = 'neklo_makeanoffer/email/accept_template';

    const EMAIL_COUNTER_TEMPLATE = 'neklo_makeanoffer/email/counter_template';

    const EMAIL_ENABLE_NOTIFY_CUSTOMER = 'neklo_makeanoffer/email/enable_notify_customer';

    const EMAIL_NOTIFY_CUSTOMER_TEMPLATE = 'neklo_makeanoffer/email/notify_customer_template';

    const EMAIL_ENABLE_NOTIFY_ADMIN = 'neklo_makeanoffer/email/enable_notify_admin';

    const EMAIL_NOTIFY_ADMIN_TEMPLATE = 'neklo_makeanoffer/email/notify_admin_template';

    const EMAIL_NOTIFY_ADMIN_RECIPIENTS = 'neklo_makeanoffer/email/admin_notify_recipients';

    const EMAIL_IDENTITY = 'neklo_makeanoffer/email/identity';

    const CRON_ENABLE = 'neklo_makeanoffer/cron/enable';

    const CRON_DELETE_AFTER = 'neklo_makeanoffer/cron/delete_after';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     * @param Context $context
     */
    public function __construct(
        Serializer $serializer,
        Context $context
    ) {
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    public function isModuleEnable($store = 0)
    {
        return $this->scopeConfig->getValue(self::GENERAL_ENABLE, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getCouponLifetime($store = 0)
    {
        return $this->scopeConfig->getValue(self::COUPON_LIFETIME, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getCouponSuffix($store = 0)
    {
        return $this->scopeConfig->getValue(self::COUPON_SUFFIX, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getCouponDash($store = 0)
    {
        return $this->scopeConfig->getValue(self::COUPON_DASH, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getCouponPrefix($store = 0)
    {
        return $this->scopeConfig->getValue(self::COUPON_PREFIX, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getCouponLength($store = 0)
    {
        return $this->scopeConfig->getValue(self::COUPON_LENGTH, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getDisplayMode($store = 0)
    {
        return $this->scopeConfig->getValue(self::FRONTEND_DISPLAY, ScopeInterface::SCOPE_STORES, $store);
    }

    /**
     * @param int $store
     *
     * @return int
     */
    public function getDisplayPriceWithTax($store = 0)
    {
        return $this->scopeConfig->getValue(self::FRONTEND_DISPLAY_PRICE_TAX, ScopeInterface::SCOPE_STORES, $store);
    }

    /**
     * @param int $store
     * @return int
     */
    public function getDisplayAnotherStoreLink($store = 0)
    {
        return $this->scopeConfig->getValue(self::FRONTEND_DISPLAY_ANOTHER_STORE_LINK, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getButtonLabel($store = 0)
    {
        return $this->scopeConfig->getValue(self::FRONTEND_BUTTON_LABEL, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getBlocTitle($store = 0)
    {
        return $this->scopeConfig->getValue(self::FRONTEND_TITLE, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getBlockShortDescription($store = 0)
    {
        return $this->scopeConfig->getValue(self::FRONTEND_DESCRIPTION, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getSuccessMessage($store = 0)
    {
        return $this->scopeConfig->getValue(
            self::FRONTEND_SUCCESS_MESSAGE,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    public function getAcceptTemplate($store = 0)
    {
        return $this->scopeConfig->getValue(self::EMAIL_ACCEPT_TEMPLATE, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getCounterTemplate($store = 0)
    {
        return $this->scopeConfig->getValue(self::EMAIL_COUNTER_TEMPLATE, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getDeclineTemplate($store = 0)
    {
        return $this->scopeConfig->getValue(self::EMAIL_DECLINE_TEMPLATE, ScopeInterface::SCOPE_STORES, $store);
    }

    public function isNotifyCustomerEnabled($store = 0)
    {
        return $this->scopeConfig->getValue(self::EMAIL_ENABLE_NOTIFY_CUSTOMER, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getNotifyCustomerTemplate($store = 0)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_NOTIFY_CUSTOMER_TEMPLATE,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    public function isNotifyAdminEnabled($store = 0)
    {
        return $this->scopeConfig->getValue(self::EMAIL_ENABLE_NOTIFY_ADMIN, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getNotifyAdminTemplate($store = 0)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_NOTIFY_ADMIN_TEMPLATE,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    public function getNotifyAdminRecipients($store = 0)
    {
        $emails = $this->scopeConfig->getValue(
            self::EMAIL_NOTIFY_ADMIN_RECIPIENTS,
            ScopeInterface::SCOPE_STORES,
            $store
        );
        return $this->serializer->unserialize($emails);
    }

    public function getEmailIdentity($store = 0)
    {
        return $this->scopeConfig->getValue(self::EMAIL_IDENTITY, ScopeInterface::SCOPE_STORES, $store);
    }

    public function isCronEnabled()
    {
        return $this->scopeConfig->getValue(self::CRON_ENABLE, ScopeInterface::SCOPE_STORES);
    }

    public function getDeleteAfter()
    {
        return $this->scopeConfig->getValue(self::CRON_DELETE_AFTER, ScopeInterface::SCOPE_STORES);
    }
}
