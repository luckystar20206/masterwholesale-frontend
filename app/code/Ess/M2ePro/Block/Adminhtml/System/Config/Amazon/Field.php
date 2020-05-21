<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\System\Config\Amazon;

/**
 * Class Field
 * @package Ess\M2ePro\Block\Adminhtml\System\Config\Amazon
 */
class Field extends \Ess\M2ePro\Block\Adminhtml\System\Config\Integration
{
    /**
     * @inheritdoc
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setValue((int)$this->moduleHelper->getConfig()->getGroupValue(
            '/component/amazon/',
            'mode'
        ));

        return parent::_getElementHtml($element);
    }
}
