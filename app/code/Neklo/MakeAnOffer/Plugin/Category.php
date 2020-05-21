<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Plugin;

class Category
{
    private $productAction;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Action $productAction)
    {
        $this->productAction = $productAction;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     */
    public function beforeSave(\Magento\Catalog\Model\Category $category)
    {
        if ($category->getAssignMakeAnOfferCategory() != false) {
            $productIds = $category->getProductCollection()->getAllIds();

            $this->productAction->updateAttributes(
                $productIds,
                ['allow_make_an_offer_product' => $category->getAllowMakeAnOfferCategory()],
                $category->getStoreId()
            );
            $category->setAssignMakeAnOfferCategory(false);
        }
    }
}
