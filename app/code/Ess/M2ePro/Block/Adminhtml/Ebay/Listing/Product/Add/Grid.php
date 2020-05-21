<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Product\Add;

/**
 * Class Grid
 * @package Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Product\Add
 */
abstract class Grid extends \Ess\M2ePro\Block\Adminhtml\Magento\Product\Grid
{
    /** @var \Ess\M2ePro\Model\Listing */
    protected $listing;

    protected $magentoProductCollectionFactory;
    protected $type;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->type = $type;
        parent::__construct($context, $backendHelper, $data);
    }

    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->listing = $this->getHelper('Data\GlobalData')->getValue('listing_for_products_add');

        $this->setId('ebayListingProductGrid'.$this->listing->getId());
        // ---------------------------------------

        $this->hideMassactionDropDown = true;
        $this->showAdvancedFilterProductsOption = false;
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection \Ess\M2ePro\Model\ResourceModel\Magento\Product\Collection */
        $collection = $this->magentoProductCollectionFactory->create()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id');

        $collection->setStoreId($this->listing->getStoreId());
        $collection->joinStockItem();

        // ---------------------------------------
        $collection->getSelect()->distinct();
        // ---------------------------------------

        // Set filter store
        // ---------------------------------------
        $store = $this->_getStore();

        if ($store->getId()) {
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'thumbnail',
                'catalog_product/thumbnail',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
            $collection->addAttributeToSelect('thumbnail');
        }
        // ---------------------------------------

        // Hide products others listings
        // ---------------------------------------
        $hideParam = true;
        if ($this->getRequest()->has('show_products_others_listings')) {
            $hideParam = false;
        }

        if ($hideParam || isset($this->listing['id'])) {
            $lpTable = $this->activeRecordFactory->getObject('Listing\Product')->getResource()->getMainTable();
            $dbExcludeSelect = $collection->getConnection()
                ->select()
                ->from($lpTable, new \Zend_Db_Expr('DISTINCT `product_id`'));

            if ($hideParam) {
                $lTable = $this->activeRecordFactory->getObject('Listing')->getResource()->getMainTable();
                $dbExcludeSelect->join(
                    ['l' => $lTable],
                    '`l`.`id` = `listing_id`',
                    null
                );

                $dbExcludeSelect->where('`l`.`account_id` = ?', $this->listing['account_id']);
                $dbExcludeSelect->where('`l`.`marketplace_id` = ?', $this->listing['marketplace_id']);
                $dbExcludeSelect->where('`l`.`component_mode` = ?', \Ess\M2ePro\Helper\Component\Ebay::NICK);
            } else {
                $dbExcludeSelect->where('`listing_id` = ?', (int)$this->listing['id']);
            }

            $collection->getSelect()
                ->joinLeft(['sq' => $dbExcludeSelect], 'sq.product_id = e.entity_id', [])
                ->where('sq.product_id IS NULL');
        }
        // ---------------------------------------

        $collection->addFieldToFilter(
            [[
                'attribute' => 'type_id',
                'in' => $this->getHelper('Magento\Product')->getOriginKnownTypes()
            ]]
        );

        $this->setCollection($collection);

        $this->getCollection()->addWebsiteNamesToResult();

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', [
            'header'    => $this->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => [$this, 'callbackColumnProductId']
        ]);

        $this->addColumn('name', [
            'header'    => $this->__('Title'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'escape'    => false,
            'frame_callback' => [$this, 'callbackColumnProductTitle']
        ]);

        $this->addColumn('type', [
            'header'    => $this->__('Type'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'type_id',
            'filter_index' => 'type_id',
            'options'   => $this->getProductTypes()
        ]);

        $this->addColumn('is_in_stock', [
            'header'    => $this->__('Stock Availability'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'is_in_stock',
            'filter_index' => 'is_in_stock',
            'options' => [
                '1' => $this->__('In Stock'),
                '0' => $this->__('Out of Stock')
            ],
            'frame_callback' => [$this, 'callbackColumnIsInStock']
        ]);

        $this->addColumn('sku', [
            'header'    => $this->__('SKU'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'text',
            'index'     => 'sku',
            'filter_index' => 'sku'
        ]);

        $store = $this->_getStore();

        $this->addColumn('price', [
            'header'    => $this->__('Price'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'price',
            'filter' => 'Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Filter\Price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'     => 'price',
            'filter_index' => 'price',
            'frame_callback' => [$this, 'callbackColumnPrice']
        ]);

        $this->addColumn('qty', [
            'header'    => $this->__('QTY'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'qty',
            'filter_index' => 'qty',
            'frame_callback' => [$this, 'callbackColumnQty']
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');

        return parent::_prepareMassaction();
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    protected function _getStore()
    {
        // Get store filter
        // ---------------------------------------
        $storeId = 0;
        if (isset($this->listing['store_id'])) {
            $storeId = (int)$this->listing['store_id'];
        }
        // ---------------------------------------

        return $this->_storeManager->getStore($storeId);
    }

    //########################################

    abstract protected function getSelectedProductsCallback();

    //########################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(<<<JS
            require([
                'M2ePro/General/PhpFunctions',
            ], function(){

                wait(function() {
                    return typeof ProductGridObj != 'undefined';
                }, function() {
                  return ProductGridObj.massactionMassSelectStyleFix();
                }, 20);
            });
JS
            );

            return parent::_toHtml();
        }

        // ---------------------------------------
        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions(
            'Ebay_Listing_AutoAction',
            ['id' => $this->listing->getId()]
        ));

        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions(
            'Ebay_Listing_Product_Add',
            ['_current' => true]
        ));

        $this->jsUrl->add(
            $this->getUrl('*/ebay_listing_product_add', ['_current' => true, 'step' => null]),
            'ebay_listing_product_add'
        );

        $this->jsUrl->add(
            $this->getUrl('*/ebay_listing_product_category_settings', ['_current' => true, 'step' => null]),
            'ebay_listing_product_category_settings'
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->jsTranslator->addTranslations([
            'eBay Categories' => $this->__('eBay Categories'),
            'of Product' => $this->__('of Product'),
            'Specifics' => $this->__('Specifics'),
            'Auto Add/Remove Rules' => $this->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => $this->__('Based on Magento Categories'),
            'You must select at least 1 Category.' =>
                $this->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' =>
                $this->__('Rule with the same Title already exists.'),
            'Listing Settings Customization' => $this->__('Listing Settings Customization'),
        ]);
        // ---------------------------------------

        // ---------------------------------------
        $showAutoActionPopup = !$this->getHelper('Module')->getConfig()->getGroupValue(
            '/view/ebay/advanced/autoaction_popup/',
            'shown'
        );
        $showAutoActionPopup = $this->getHelper('Data')->jsonEncode($showAutoActionPopup);

        // ---------------------------------------

        $this->js->add(
            <<<JS
    require([
        'M2ePro/Ebay/Listing/Product/Add',
        'M2ePro/Plugin/AreaWrapper',
        'M2ePro/Plugin/ProgressBar',
        'M2ePro/Ebay/Listing/AutoAction'
    ], function(){

        window.WrapperObj = new AreaWrapper('add_products_container');
        window.ProgressBarObj = new ProgressBar('add_products_progress_bar');

        window.ListingProductAddObj = new EbayListingProductAdd({
            show_autoaction_popup: {$showAutoActionPopup},

            get_selected_products: {$this->getSelectedProductsCallback()}
        });

        window.ListingAutoActionObj = new EbayListingAutoAction();

        wait(function() {
            return typeof ProductGridObj != 'undefined';
        }, function() {
          return ProductGridObj.massactionMassSelectStyleFix();
        }, 20);
    });
JS
        );

        return parent::_toHtml();
    }

    //########################################

    protected function getProductTypes()
    {
        $magentoProductTypes = $this->type->getOptionArray();
        $knownTypes = $this->getHelper('Magento\Product')->getOriginKnownTypes();

        foreach ($magentoProductTypes as $type => $magentoProductTypeLabel) {
            if (in_array($type, $knownTypes)) {
                continue;
            }

            unset($magentoProductTypes[$type]);
        }

        return $magentoProductTypes;
    }

    //########################################
}
