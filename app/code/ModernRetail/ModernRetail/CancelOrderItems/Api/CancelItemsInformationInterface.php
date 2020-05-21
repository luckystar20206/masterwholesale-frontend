<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ModernRetail\CancelOrderItems\Api;

/**
 * Interface CancelItemsInformationInterface
 * @api
 * @since 100.1.3
 */
interface CancelItemsInformationInterface
{
    /**
     * Gets the item id.
     *
     * @api
     * @return string
     */
    public function getItemId();

    /**
     * Sets the item id.
     *
     * @api
     * @param int item_id
     */
    public function setItemId($itemId);

    /**
     * Gets the quantity.
     *
     * @api
     * @return string
     */
    public function getQty();

    /**
     * Sets the quantity.
     *
     * @api
     * @param int $qty
     * @return void
     */
    public function setQty($qty);

}
