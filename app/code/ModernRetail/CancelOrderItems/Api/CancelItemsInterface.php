<?php
namespace ModernRetail\CancelOrderItems\Api;

use ModernRetail\CancelOrderItems\Api\CancelItemsInformationInterface;

interface CancelItemsInterface {

 /**
     *
     * @api
     *
     * @param string  $orderId
     * @param string  $items
     *
     * @return \ModernRetail\CancelOrderItems\Api\CancelItemsInterface
     */

    public function execute(
        string  $orderId,
        string  $items
    );
}