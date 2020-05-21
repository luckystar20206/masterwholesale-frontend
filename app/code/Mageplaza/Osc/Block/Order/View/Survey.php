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

namespace Mageplaza\Osc\Block\Order\View;

/**
 * Class Survey
 * @package Mageplaza\Osc\Block\Order\View
 */
class Survey extends AbstractView
{
    /**
     * @return string
     */
    public function getSurveyQuestion()
    {
        if ($order = $this->getOrder()) {
            return $order->getOscSurveyQuestion();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSurveyAnswers()
    {
        if ($order = $this->getOrder()) {
            return $order->getOscSurveyAnswers();
        }

        return '';
    }
}
