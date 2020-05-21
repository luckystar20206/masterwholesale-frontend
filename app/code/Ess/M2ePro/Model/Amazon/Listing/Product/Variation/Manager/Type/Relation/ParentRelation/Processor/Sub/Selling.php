<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager\Type\Relation\ParentRelation\Processor\Sub;

use \Ess\M2ePro\Model\Amazon\Listing\Product;

/**
 * Class Selling
 * @package Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager\Type\Relation\ParentRelation\Processor\Sub
 */
class Selling extends AbstractModel
{
    //########################################

    protected function check()
    {
        return null;
    }

    protected function execute()
    {
        $qty           = null;
        $regularPrice  = null;
        $businessPrice = null;

        $afnState = null;
        $repricingState = null;
        $isAfn = Product::IS_AFN_CHANNEL_NO;
        $isRepricing = Product::IS_REPRICING_NO;
        $repricingEnabled = $repricingDisabled = $afnCount = $totalCount = 0;

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {
            if ($listingProduct->isNotListed() || $listingProduct->isBlocked()) {
                continue;
            }

            $totalCount++;

            /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if ($amazonListingProduct->isRepricingUsed()) {
                $isRepricing = Product::IS_REPRICING_YES;

                $amazonListingProduct->isRepricingDisabled() && $repricingDisabled++;
                $amazonListingProduct->isRepricingEnabled() && $repricingEnabled++;
            }

            if ($amazonListingProduct->isAfnChannel()) {
                $isAfn = Product::IS_AFN_CHANNEL_YES;
                $afnCount++;
            } else {
                $qty = (int)$qty + (int)$amazonListingProduct->getOnlineQty();
            }

            $regularSalePrice = $amazonListingProduct->getOnlineRegularSalePrice();
            $actualOnlineRegularPrice = $amazonListingProduct->getOnlineRegularPrice();

            if ($regularSalePrice > 0) {
                $startDateTimestamp = strtotime($amazonListingProduct->getOnlineRegularSalePriceStartDate());
                $endDateTimestamp   = strtotime($amazonListingProduct->getOnlineRegularSalePriceEndDate());

                $currentTimestamp = strtotime($this->getHelper('Data')->getCurrentGmtDate(false, 'Y-m-d 00:00:00'));

                if ($currentTimestamp >= $startDateTimestamp &&
                    $currentTimestamp <= $endDateTimestamp &&
                    $regularSalePrice < $actualOnlineRegularPrice
                ) {
                    $actualOnlineRegularPrice = $regularSalePrice;
                }
            }

            if ($regularPrice === null || $regularPrice > $actualOnlineRegularPrice) {
                $regularPrice = $actualOnlineRegularPrice;
            }

            if ($businessPrice === null || $businessPrice > $amazonListingProduct->getOnlineBusinessPrice()) {
                $businessPrice = $amazonListingProduct->getOnlineBusinessPrice();
            }
        }($afnCount == 0) && $afnState = Product::VARIATION_PARENT_IS_AFN_STATE_ALL_NO;
        ($afnCount > 0) && $afnState = Product::VARIATION_PARENT_IS_AFN_STATE_PARTIAL;
        ($afnCount == $totalCount) && $afnState = Product::VARIATION_PARENT_IS_AFN_STATE_ALL_YES;

        $totalOnRepricing = $repricingDisabled + $repricingEnabled;
        ($totalOnRepricing == 0) && $repricingState = Product::VARIATION_PARENT_IS_REPRICING_STATE_ALL_NO;
        ($totalOnRepricing > 0) && $repricingState = Product::VARIATION_PARENT_IS_REPRICING_STATE_PARTIAL;
        ($totalOnRepricing == $totalCount) && $repricingState = Product::VARIATION_PARENT_IS_REPRICING_STATE_ALL_YES;

        $this->getProcessor()->getListingProduct()->getChildObject()->addData([
            'online_qty'                       => $qty,
            'online_regular_price'             => $regularPrice,
            'online_business_price'            => $businessPrice,
            'is_afn_channel'                   => $isAfn,
            'is_repricing'                     => $isRepricing,
            'variation_parent_afn_state'       => $afnState,
            'variation_parent_repricing_state' => $repricingState
        ]);

        $this->getProcessor()->getListingProduct()->setSetting(
            'additional_data',
            'repricing_enabled_count',
            $repricingEnabled
        );
        $this->getProcessor()->getListingProduct()->setSetting(
            'additional_data',
            'repricing_disabled_count',
            $repricingDisabled
        );
        $this->getProcessor()->getListingProduct()->setSetting(
            'additional_data',
            'afn_count',
            $afnCount
        );
    }

    //########################################
}
