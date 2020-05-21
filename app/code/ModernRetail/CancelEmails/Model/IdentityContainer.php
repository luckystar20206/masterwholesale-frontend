<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ModernRetail\CancelEmails\Model;

class IdentityContainer extends \Magento\Sales\Model\Order\Email\Container\Container
{
    const XML_PATH_EMAIL_COPY_METHOD = 'sales_email/cancel/copy_method';
    const XML_PATH_EMAIL_COPY_TO = 'sales_email/cancel/copy_to';
    const XML_PATH_EMAIL_IDENTITY = 'sales_email/cancel/identity';
    const XML_PATH_EMAIL_GUEST_TEMPLATE = 'sales_email/cancel/template';
    const XML_PATH_EMAIL_TEMPLATE = 'sales_email/cancel/template';
    const XML_PATH_EMAIL_ENABLED = 'sales_email/cancel/enabled';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        $data = $this->getConfigValue(self::XML_PATH_EMAIL_COPY_TO, $this->getStore()->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getCopyMethod()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStore()->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getGuestTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getEmailIdentity()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_IDENTITY, $this->getStore()->getStoreId());
    }
}
