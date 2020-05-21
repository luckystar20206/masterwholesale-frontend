<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Pricing\Render;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;

class FinalPriceBox
{
    public function aroundGetPriceType(
        \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox $subject,
        $proceed,
        $priceCode
    ) {
        if ($subject->getSaleableItem()->hasSdcpPriceInfo()) {
            $subject->setCacheLifetime(0);
            return $subject->getSaleableItem()->getSdcpPriceInfo()->getPrice($priceCode);
        }
        return $proceed($priceCode);
    }
    public function afterHasSpecialPrice(
        \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox $subject,
        $result
    ) {
        $item = $subject->getSaleableItem();
        if ($item->hasSdcpPriceInfo()) {
            $regularPrice = $item->getSdcpPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getValue();
            $finalPrice = $item->getSdcpPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
            if ($finalPrice < $regularPrice) {
                return true;
            }
            return false;
        }
        return $result;
    }
}
