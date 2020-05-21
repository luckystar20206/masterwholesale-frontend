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

namespace Mageplaza\Osc\Block;

use Magento\Framework\View\Element\Template;
use Mageplaza\Osc\Helper\Data as OscHelper;

/**
 * Class Container
 * @package Mageplaza\Osc\Block
 */
class Container extends Template
{
    /**
     * @var OscHelper
     */
    protected $_oscHelper;

    /**
     * Container constructor.
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
     * @return mixed
     */
    public function getCheckoutDescription()
    {
        return $this->_oscHelper->getConfigGeneral('description');
    }
}
