<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Magento\Renderer;

/**
 * Class AbstractRenderer
 * @package Ess\M2ePro\Block\Adminhtml\Magento\Renderer
 */
abstract class AbstractRenderer
{
    protected $helperFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Helper\Factory $helperFactory
    ) {
        $this->helperFactory = $helperFactory;
    }

    //########################################

    abstract public function render();

    //########################################
}
