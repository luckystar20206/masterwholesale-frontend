<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Block\System;

class Contact extends \Magento\Config\Block\System\Config\Form\Fieldset
{

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        return parent::_getHeaderHtml($element) . $this->_getAfterHeaderHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getAfterHeaderHtml()
    {
        $subscribeButton = $this->getLayout()
            ->createBlock('\Neklo\Core\Block\System\Contact\Header', 'neklo_core_contact_header');
        $subscribeButton->setTemplate('system/contact/header.phtml');

        return $subscribeButton->toHtml();
    }
}
