<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Listing\Other\Mapping;

/**
 * Class Grid
 * @package Ess\M2ePro\Block\Adminhtml\Listing\Other\Mapping
 */
class Grid extends \Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractGrid
{
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
        $this->setId('listingOtherMappingGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        /** @var $collection \Ess\M2ePro\Model\ResourceModel\Magento\Product\Collection */
        $collection = $this->magentoProductCollectionFactory->create()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id');

        $collection->joinStockItem();

        $collection->addFieldToFilter(
            [[
                'attribute' => 'type_id',
                'in' => $this->getHelper('Magento\Product')->getOriginKnownTypes()
            ]]
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', [
            'header'       => $this->__('Product ID'),
            'align'        => 'right',
            'type'         => 'number',
            'width'        => '100px',
            'index'        => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => [$this, 'callbackColumnProductId']
        ]);

        $this->addColumn('title', [
            'header'       => $this->__('Product Title / Product SKU'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '200px',
            'index'        => 'name',
            'filter_index' => 'name',
            'escape'       => false,
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle']
        ]);

        $this->addColumn('type', [
            'header'    => $this->__('Type'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'type_id',
            'filter_index' => 'type_id',
            'options' => $this->getProductTypes()
        ]);

        $this->addColumn('stock_availability', [
            'header'=> $this->__('Stock Availability'),
            'width' => '100px',
            'index' => 'is_in_stock',
            'filter_index' => 'is_in_stock',
            'type'  => 'options',
            'sortable'  => false,
            'options' => [
                1 => $this->__('In Stock'),
                0 => $this->__('Out of Stock')
            ],
            'frame_callback' => [$this, 'callbackColumnIsInStock']
        ]);

        $this->addColumn('actions', [
            'header'       => $this->__('Actions'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '125px',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => [$this, 'callbackColumnActions'],
        ]);
    }

    //########################################

    public function callbackColumnProductId($productId, $product, $column, $isExport)
    {
        $url = $this->getUrl('catalog/product/edit', ['id' => $productId]);
        $withoutImageHtml = '<a href="'.$url.'" target="_blank">'.$productId.'</a>&nbsp;';

        $showProductsThumbnails = (bool)(int)$this->getHelper('Module')->getConfig()->getGroupValue(
            '/view/',
            'show_products_thumbnails'
        );
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct \Ess\M2ePro\Model\Magento\Product */
        $magentoProduct = $this->modelFactory->getObject('Magento\Product');
        $magentoProduct->setProduct($product);

        $imageUrlResized = $magentoProduct->getThumbnailImage();
        if ($imageUrlResized === null) {
            return $withoutImageHtml;
        }

        $imageUrlResizedUrl = $imageUrlResized->getUrl();

        $imageHtml = $productId.'<div style="margin-top: 5px">'.
            '<img style="max-width: 100px; max-height: 100px;" src="' .$imageUrlResizedUrl. '" /></div>';
        $withImageHtml = str_replace('>'.$productId.'<', '>'.$imageHtml.'<', $withoutImageHtml);

        return $withImageHtml;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<div style="margin-left: 3px">'.$this->getHelper('Data')->escapeHtml($value);

        $tempSku = $row->getData('sku');
        if ($tempSku === null) {
            $tempSku = $this->modelFactory->getObject('Magento\Product')
                                          ->setProductId($row->getData('entity_id'))->getSku();
        }

        $value .= '<br/><strong>'.$this->__('SKU').':</strong> ';
        $value .= $this->getHelper('Data')->escapeHtml($tempSku).'</div>';

        return $value;
    }

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        return '<div style="margin-left: 3px">'.$this->getHelper('Data')->escapeHtml($value).'</div>';
    }

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if ($row->getData('is_in_stock') === null) {
            return $this->__('N/A');
        }

        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$this->__('Out of Stock').'</span>';
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $return = '&nbsp;<a href="javascript:void(0);" ';
        $return .= 'onclick="$(\'product_id\').setValue(\''.$row->getId().'\'); ';
        $return .= '$(\'sku\').setValue(\'\'); ';
        $return .= '$$(\'.mapping_submit_button\')[0].click(); ">';
        $return .= $this->__('Map To This Product');
        $return .= '</a>';
        return $return;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute'=>'sku','like'=>'%'.$value.'%'],
                ['attribute'=>'name', 'like'=>'%'.$value.'%']
            ]
        );
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->js->addOnReadyJs(<<<JS

        $$('#listingOtherMappingGrid div.grid th').each(function(el) {
            el.style.padding = '2px 4px';
        });

        $$('#listingOtherMappingGrid div.grid td').each(function(el) {
            el.style.padding = '2px 4px';
        });

JS
        );
        return parent::_beforeToHtml();
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/listing_other_mapping/mapGrid', ['_current'=>true]);
    }

    public function getRowUrl($row)
    {
        return false;
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
