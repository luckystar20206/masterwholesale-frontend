<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Block\System\Newsletter;

class Subscribe extends \Magento\Config\Block\System\Config\Form\Field
{

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setScope(false);
        $element->setCanUseWebsiteValue(false);
        $element->setCanUseDefaultValue(false);

        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $subscribeButton = $this->getLayout()
            ->createBlock('\Neklo\Core\Block\System\Newsletter\Subscribe\Button', 'neklo_core_subscribe');
        $subscribeButton->setTemplate('system/subscribe/button.phtml');
        $subscribeButton->setContainerId($element->getContainer()->getHtmlId());

        return $subscribeButton->toHtml();
    }
}
