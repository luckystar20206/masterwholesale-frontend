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

namespace Mageplaza\Osc\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Mageplaza\Osc\Helper\Data as OscHelper;

/**
 * Class CompatibleConfig
 * @package Mageplaza\Osc\Block\Checkout
 */
class CompatibleConfig extends Template
{
    /**
     * @var string $_template
     */
    protected $_template = 'Mageplaza_Osc::onepage/compatible-config.phtml';

    /**
     * @var OscHelper
     */
    protected $_oscHelper;

    /**
     * CompatibleConfig constructor.
     *
     * @param Template\Context $context
     * @param OscHelper $oscHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        OscHelper $oscHelper,
        array $data = []
    ) {
        $this->_oscHelper = $oscHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnableModulePostNL()
    {
        return $this->_oscHelper->isEnableModulePostNL();
    }
}
