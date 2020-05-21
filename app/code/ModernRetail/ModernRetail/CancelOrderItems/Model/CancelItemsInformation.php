<?php
namespace ModernRetail\CancelOrderItems\Model;

use ModernRetail\CancelOrderItems\Api\CancelItemsInformationInterface;

/**
 * Model that contains updated cart information.
 */
class CancelItemsInformation implements CancelItemsInformationInterface{

    /**
     * The sku for this cart entry.
     * @var string
     */
    protected $itemId;

    /**
     * The quantity value for this cart entry.
     * @var int
     */
    protected $qty;

    /**
     * Gets the sku.
     *
     * @api
     * @return string
     */
    public function getItemId() {
        return $this->itemId;
    }

    /**
     * Sets the sku.
     *
     * @api
     * @param int $sku
     */
    public function setItemId($itemId) {
        $this->itemId = $itemId;
    }

    /**
     * Gets the quantity.
     *
     * @api
     * @return string
     */
    public function getQty() {
        return $this->qty;
    }

    /**
     * Sets the quantity.
     *
     * @api
     * @param int $qty
     * @return void
     */
    public function setQty($qty) {
        $this->qty = $qty;
    }
}