<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DeliveryTime\Api\Data;

/**
 * Interface DeliveryTime
 * @api
 */
interface DeliveryTimeInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    const DELIVERY_DATE       = 'deliveryDate';
    const DELIVERY_TIME       = 'deliveryTime';
    const HOUSE_SECURITY_CODE = 'houseSecurityCode';
    const DELIVERY_COMMENT    = 'deliveryComment';

    /**
     * @return string
     */
    public function getDeliveryDate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDeliveryDate($value);

    /**
     * @return string
     */
    public function getDeliveryTime();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDeliveryTime($value);

    /**
     * @return string
     */
    public function getHouseSecurityCode();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setHouseSecurityCode($value);

    /**
     * @return string
     */
    public function getDeliveryComment();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDeliveryComment($value);
}
