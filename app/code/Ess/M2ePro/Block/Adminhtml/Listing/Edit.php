<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Listing;

use Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractContainer;

/**
 * Class Edit
 * @package Ess\M2ePro\Block\Adminhtml\Listing
 */
class Edit extends AbstractContainer
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_listing';

        parent::_construct();
    }
}
