<?php

namespace ModernRetail\CancelEmails\Block\Order\Email;

class Items extends \Magento\Sales\Block\Order\Email\Items
{
    public function getRendererTemplate()
    {
        return 'ModernRetail_CancelEmails::email/items/order/default.phtml';
    }

}