<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Block\System\Newsletter\Subscribe;

class Button extends \Magento\Backend\Block\Template
{

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getButton()
    {
        $button = $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button');
        $button
            ->setType('button')
            ->setLabel(__('Subscribe'))
            ->setStyle("width:100%; box-sizing: border-box;")
            ->setId('neklo_core_subscribe');

        return $button;
    }

    /**
     * @return string
     */
    public function getButtonHtml()
    {
        return $this->getButton()->toHtml();
    }
}
