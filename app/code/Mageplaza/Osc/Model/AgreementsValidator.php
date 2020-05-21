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

namespace Mageplaza\Osc\Model;

use Mageplaza\Osc\Helper\Data as OscHelper;

/**
 * Class AgreementsValidator
 * @package Mageplaza\Osc\Model
 */
class AgreementsValidator extends \Magento\CheckoutAgreements\Model\AgreementsValidator
{
    /**
     * @var OscHelper
     */
    protected $_oscHelper;

    /**
     * AgreementsValidator constructor.
     *
     * @param OscHelper $oscHelper
     * @param null $list
     */
    public function __construct(
        OscHelper $oscHelper,
        $list = null
    ) {
        $this->_oscHelper = $oscHelper;

        parent::__construct($list);
    }

    /**
     * @param array $agreementIds
     *
     * @return bool
     */
    public function isValid($agreementIds = [])
    {
        if (!$this->_oscHelper->isEnabledTOC()) {
            return true;
        }

        return parent::isValid($agreementIds);
    }
}
