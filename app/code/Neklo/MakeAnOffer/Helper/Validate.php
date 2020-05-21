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
use Magento\Captcha\Helper\Data;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Framework\App\RequestInterface;

class Validate extends AbstractHelper
{
    /**
     * @var Data
     */
    private $captchaHelper;

    /**
     * @var CaptchaStringResolver
     */
    private $captchaStringResolver;

    /**
     * Validate constructor.
     * @param Context $context
     * @param Data $captchaHelper
     * @param CaptchaStringResolver $captchaStringResolver
     */
    public function __construct(
        Context $context,
        Data $captchaHelper,
        CaptchaStringResolver $captchaStringResolver
    ) {
        $this->captchaHelper = $captchaHelper;
        $this->captchaStringResolver = $captchaStringResolver;
        parent::__construct($context);
    }

    /**
     * @param $email
     * @param $link
     * @param $requestPrice
     * @param $qty
     * @param $productId
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateData($email, $link, $requestPrice, $qty, $productId)
    {
        $error = false;

        if (\Zend_Validate::is($link, 'NotEmpty') && !$this->isValidUrl($link)) {
            $error = true;
        }
        if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
            $error = true;
        }

        if (!\Zend_Validate::is($requestPrice, 'NotEmpty') || !$this->validatePrice($requestPrice)) {
            $error = true;
        }

        if (!\Zend_Validate::is($qty, 'NotEmpty') || !$this->validateInt($qty)) {
            $error = true;
        }

        if (!\Zend_Validate::is($productId, 'NotEmpty') || !$this->validateInt($productId)) {
            $error = true;
        }

        if (!$error) {
            return true;
        }

        return false;
    }

    /**
     * @param $request
     */
    public function validateCaptcha($request)
    {
        $formId = 'make_an_offer_form';
        $captcha = $this->captchaHelper->getCaptcha($formId);
        if ($captcha->isRequired()) {
            if (!$captcha->isCorrect($this->captchaStringResolver->resolve($request, $formId))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $int
     * @return boolean
     */
    private function validateInt($int)
    {
        return preg_match("/^[1-9][0-9]*$/", $int);
    }

    /**
     * @param $price
     * @return boolean
     */
    private function validatePrice($price)
    {
        return preg_match("/^([1-9][0-9]*(\.[0-9]+)?|0?\.[0-9]*[1-9][0-9]*)$/", $price);
    }

    /**
     * @param $url
     * @return bool
     */
    private function isValidUrl($url)
    {
        preg_match('/[a-z0-9+!*(),;?&=$_.-]+'
            . '(:[a-z0-9+!*(),;?&=$_.-]+)?'
            . '@?[a-z0-9\-\.]*\.[a-z]{2,4}|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $url, $matches);

        return !empty($matches);
    }
}
