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

namespace Mageplaza\Osc\Api\Data;

/**
 * Interface OscDetailsInterface
 * @api
 */
interface OscDetailsInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    const SHIPPING_METHODS = 'shipping_methods';
    const PAYMENT_METHODS  = 'payment_methods';
    const TOTALS           = 'totals';
    const REDIRECT_URL     = 'redirect_url';
    const IMAGE_DATA       = 'image_data';
    const OPTIONS          = 'options';
    const REQUEST_PATH     = 'request_path';

    /**
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingMethods();

    /**
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface[] $shippingMethods
     *
     * @return $this
     */
    public function setShippingMethods($shippingMethods);

    /**
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[]
     */
    public function getPaymentMethods();

    /**
     * @param \Magento\Quote\Api\Data\PaymentMethodInterface[] $paymentMethods
     *
     * @return $this
     */
    public function setPaymentMethods($paymentMethods);

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals();

    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     *
     * @return $this
     */
    public function setTotals($totals);

    /**
     * @return string
     */
    public function getRedirectUrl();

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setRedirectUrl($url);

    /**
     * @return string
     */
    public function getImageData();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setImageData($value);

    /**
     * @return string
     */
    public function getOptions();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setOptions($value);

    /**
     * @return string
     */
    public function getRequestPath();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setRequestPath($value);
}
