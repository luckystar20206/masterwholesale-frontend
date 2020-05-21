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

namespace Mageplaza\Osc\Model\Plugin\Customer;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement as AM;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Address
 * @package Mageplaza\Osc\Model\Plugin\Customer
 */
class AccountManagement
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Config
     */
    private $config;

    /**
     * AccountManagement constructor.
     *
     * @param Session $checkoutSession
     * @param Config $config
     */
    public function __construct(Session $checkoutSession, Config $config)
    {
        $this->checkoutSession = $checkoutSession;
        $this->config          = $config;
    }

    /**
     * @param AM $subject
     * @param CustomerInterface $customer
     * @param null $password
     * @param string $redirectUrl
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeCreateAccount(AM $subject, CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        $oscData = $this->checkoutSession->getOscData();
        if (!empty($oscData['register']) && !empty($oscData['password'])) {
            $password = $oscData['password'];

            if (count($oscData['customerAttributes'])) {
                foreach ($oscData['customerAttributes'] as $key => $value) {
                    if ($this->config->getAttribute('customer', $key)) {
                        $customer->setData($key, $value);
                    }
                }
            }
        }

        return [$customer, $password, $redirectUrl];
    }
}
