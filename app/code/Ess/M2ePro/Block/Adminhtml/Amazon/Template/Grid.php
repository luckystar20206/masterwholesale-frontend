<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Template;

use Magento\Framework\DB\Select;

/**
 * Class Grid
 * @package Ess\M2ePro\Block\Adminhtml\Amazon\Template
 */
class Grid extends \Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    const TEMPLATE_SELLING_FORMAT    = 'selling_format';
    const TEMPLATE_SYNCHRONIZATION   = 'synchronization';
    const TEMPLATE_SHIPPING_OVERRIDE = 'shipping_override';
    const TEMPLATE_SHIPPING_TEMPLATE = 'shipping_template';
    const TEMPLATE_DESCRIPTION       = 'description';
    const TEMPLATE_PRODUCT_TAX_CODE  = 'product_tax_code';

    protected $wrapperCollectionFactory;
    protected $amazonFactory;
    protected $resourceConnection;

    private $enabledMarketplacesCollection = null;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->wrapperCollectionFactory = $wrapperCollectionFactory;
        $this->amazonFactory            = $amazonFactory;
        $this->resourceConnection       = $resourceConnection;
        parent::__construct($context, $backendHelper, $data);
    }

    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->css->addFile('policy/grid.css');

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        // Prepare selling format collection
        // ---------------------------------------
        $collectionSellingFormat = $this->activeRecordFactory->getObject('Template\SellingFormat')->getCollection();
        $collectionSellingFormat->getSelect()->reset(Select::COLUMNS);
        $collectionSellingFormat->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('\''.self::TEMPLATE_SELLING_FORMAT.'\' as `type`'),
                new \Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date',
                'update_date',
                new \Zend_Db_Expr('NULL as `category_path`'),
                new \Zend_Db_Expr('NULL as `browsenode_id`'),
                new \Zend_Db_Expr('NULL as `is_new_asin_accepted`')
            ]
        );
        $collectionSellingFormat->getSelect()->where('component_mode = (?)', \Ess\M2ePro\Helper\Component\Amazon::NICK);
        // ---------------------------------------

        // Prepare synchronization collection
        // ---------------------------------------
        $collectionSynchronization = $this->activeRecordFactory->getObject('Template\Synchronization')->getCollection();
        $collectionSynchronization->getSelect()->reset(Select::COLUMNS);
        $collectionSynchronization->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('\''.self::TEMPLATE_SYNCHRONIZATION.'\' as `type`'),
                new \Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date',
                'update_date',
                new \Zend_Db_Expr('NULL as `category_path`'),
                new \Zend_Db_Expr('NULL as `browsenode_id`'),
                new \Zend_Db_Expr('NULL as `is_new_asin_accepted`')
            ]
        );
        $collectionSynchronization->getSelect()->where(
            'component_mode = (?)',
            \Ess\M2ePro\Helper\Component\Amazon::NICK
        );
        // ---------------------------------------

        // Prepare shipping override collection
        // ---------------------------------------
        $collectionShippingOverride = $this->activeRecordFactory->getObject('Amazon_Template_ShippingOverride')
            ->getCollection();
        $collectionShippingOverride->getSelect()->reset(Select::COLUMNS);
        $collectionShippingOverride->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('\''.self::TEMPLATE_SHIPPING_OVERRIDE.'\' as `type`'),
                'marketplace_id',
                'create_date',
                'update_date',
                new \Zend_Db_Expr('NULL as `category_path`'),
                new \Zend_Db_Expr('NULL as `browsenode_id`'),
                new \Zend_Db_Expr('NULL as `is_new_asin_accepted`')
            ]
        );
        // ---------------------------------------

        // Prepare Shipping Template collection
        // ---------------------------------------
        $collectionShippingTemplate = $this->activeRecordFactory->getObject('Amazon_Template_ShippingTemplate')
            ->getCollection();
        $collectionShippingTemplate->getSelect()->reset(Select::COLUMNS);
        $collectionShippingTemplate->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('\''.self::TEMPLATE_SHIPPING_TEMPLATE.'\' as `type`'),
                new \Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date',
                'update_date',
                new \Zend_Db_Expr('NULL as `category_path`'),
                new \Zend_Db_Expr('NULL as `browsenode_id`'),
                new \Zend_Db_Expr('NULL as `is_new_asin_accepted`')
            ]
        );
        // ---------------------------------------

        // Prepare shipping override collection
        // ---------------------------------------
        $collectionDescription = $this->amazonFactory->getObject('Template\Description')->getCollection();

        $collectionDescription->getSelect()->join(
            ['mm' => $this->activeRecordFactory->getObject('Marketplace')->getResource()->getMainTable()],
            'mm.id=second_table.marketplace_id',
            []
        );

        $collectionDescription->addFieldToFilter('mm.status', \Ess\M2ePro\Model\Marketplace::STATUS_ENABLE);

        $collectionDescription->getSelect()->reset(Select::COLUMNS);
        $collectionDescription->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('\''.self::TEMPLATE_DESCRIPTION.'\' as `type`'),
                'second_table.marketplace_id',
                'create_date',
                'update_date',
                'second_table.category_path',
                'second_table.browsenode_id',
                'second_table.is_new_asin_accepted'
            ]
        );
        // ---------------------------------------

        // Prepare description collection
        // ---------------------------------------
        $collectionProductTaxCode = $this->activeRecordFactory->getObject('Amazon_Template_ProductTaxCode')
            ->getCollection();

        $collectionProductTaxCode->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collectionProductTaxCode->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('\'' . self::TEMPLATE_PRODUCT_TAX_CODE . '\' as `type`'),
                new \Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date',
                'update_date',
                new \Zend_Db_Expr('NULL as `category_path`'),
                new \Zend_Db_Expr('NULL as `browsenode_id`'),
                new \Zend_Db_Expr('NULL as `is_new_asin_accepted`')
            ]
        );
        // ---------------------------------------

        // Prepare union select
        // ---------------------------------------
        $collectionsArray = [
            $collectionSellingFormat->getSelect(),
            $collectionSynchronization->getSelect(),
            $collectionDescription->getSelect(),
            $collectionShippingOverride->getSelect(),
            $collectionShippingTemplate->getSelect(),
            $collectionProductTaxCode->getSelect()
        ];

        $unionSelect = $this->resourceConnection->getConnection()->select();
        $unionSelect->union($collectionsArray);
        // ---------------------------------------

        // Prepare result collection
        // ---------------------------------------
        /** @var \Ess\M2ePro\Model\ResourceModel\Collection\Wrapper $resultCollection */
        $resultCollection = $this->wrapperCollectionFactory->create();
        $resultCollection->setConnection($this->resourceConnection->getConnection());
        $resultCollection->getSelect()->reset()->from(
            ['main_table' => $unionSelect],
            [
                'template_id',
                'title',
                'type',
                'marketplace_id',
                'create_date',
                'update_date',
                'category_path',
                'browsenode_id',
                'is_new_asin_accepted'
            ]
        );
        // ---------------------------------------

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareColumns()
    {

        $this->addColumn('title', [
            'header'        => $this->__('Title'),
            'align'         => 'left',
            'type'          => 'text',
//            'width'         => '150px',
            'index'         => 'title',
            'escape'        => true,
            'filter_index'  => 'main_table.title'
        ]);

        $options = [
            self::TEMPLATE_SELLING_FORMAT => $this->__('Selling'),
            self::TEMPLATE_SYNCHRONIZATION => $this->__('Synchronization')
        ];
        $this->addColumn('type', [
            'header'        => $this->__('Type'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '120px',
            'sortable'      => false,
            'index'         => 'type',
            'filter_index'  => 'main_table.type',
            'options'       => $options
        ]);

        $this->addColumn('marketplace', [
            'header'        => $this->__('Marketplace'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'marketplace_id',
            'filter_index'  => 'marketplace_id',
            'filter_condition_callback' => [$this, 'callbackFilterMarketplace'],
            'frame_callback'=> [$this, 'callbackColumnMarketplace'],
            'options'       => $this->getEnabledMarketplaceTitles()
        ], 'type');

        $this->addColumn('create_date', [
            'header'    => $this->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'filter'    => '\Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime',
            'filter_time' => true,
            'format'    => \IntlDateFormatter::MEDIUM,
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ]);

        $this->addColumn('update_date', [
            'header'    => $this->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'filter'    => '\Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime',
            'filter_time' => true,
            'format'    => \IntlDateFormatter::MEDIUM,
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ]);

        $this->addColumn('actions', [
            'header'    => $this->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => '\Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Renderer\Action',
            'getter'    => 'getTemplateId',
            'actions'   => [
                [
                    'caption'   => $this->__('Delete'),
                    'class'     => 'action-default scalable add primary policy-delete-btn',
                    'url'       => [
                        'base' => '*/amazon_template/delete',
                        'params' => [
                            'type' => '$type'
                        ]
                    ],
                    'field'    => 'id',
                    'confirm'  => $this->__('Are you sure?')
                ]
            ]
        ]);

        parent::_prepareColumns();

        $options = [
            self::TEMPLATE_SELLING_FORMAT    => $this->__('Selling'),
            self::TEMPLATE_DESCRIPTION       => $this->__('Description'),
            self::TEMPLATE_SYNCHRONIZATION   => $this->__('Synchronization'),
            self::TEMPLATE_SHIPPING_TEMPLATE => $this->__('Shipping Template'),
            self::TEMPLATE_SHIPPING_OVERRIDE => $this->__('Shipping Override'),
            self::TEMPLATE_PRODUCT_TAX_CODE  => $this->__('Product Tax Code'),
        ];

        $this->getColumn('type')->setData('options', $options);

        $this->getColumn('title')->setData('header', $this->__('Title / Description Policy Category'));
        $this->getColumn('title')->setData('frame_callback', [$this, 'callbackColumnTitle']);
        $this->getColumn('title')->setData('filter_condition_callback', [$this, 'callbackFilterTitle']);

        return $this;
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        if ($row->getData('type') != self::TEMPLATE_DESCRIPTION) {
            return $value;
        }

        $title = $this->getHelper('Data')->escapeHtml($value);

        $categoryWord = $this->__('Category');
        $categoryPath = !empty($row['category_path']) ? "{$row['category_path']} ({$row['browsenode_id']})"
            : $this->__('Not Set');

        $newAsin = $this->__('New ASIN/ISBN');
        $newAsinAccepted = $this->__('No');
        if ($row->getData('is_new_asin_accepted') == 1) {
            $newAsinAccepted = $this->__('Yes');
        }

        return <<<HTML
{$title}
<div>
    <span style="font-weight: bold">{$newAsin}</span>: <span style="color: #505050">{$newAsinAccepted}</span><br/>
    <span style="font-weight: bold">{$categoryWord}</span>: <span style="color: #505050">{$categoryPath}</span><br/>
</div>
HTML;
    }

    public function callbackColumnMarketplace($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return $this->__('Any');
        }

        return $value;
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'category_path LIKE ? OR browsenode_id LIKE ? OR title LIKE ?',
            '%'. $value .'%'
        );
    }

    protected function callbackFilterMarketplace($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('marketplace_id = 0 OR marketplace_id = ?', (int)$value);
    }

    //########################################

    private function getEnabledMarketplacesCollection()
    {
        if ($this->enabledMarketplacesCollection === null) {
            $collection = $this->activeRecordFactory->getObject('Marketplace')->getCollection();
            $collection->addFieldToFilter('component_mode', \Ess\M2ePro\Helper\Component\Amazon::NICK);
            $collection->addFieldToFilter('status', \Ess\M2ePro\Model\Marketplace::STATUS_ENABLE);
            $collection->setOrder('sorder', 'ASC');

            $this->enabledMarketplacesCollection = $collection;
        }

        return $this->enabledMarketplacesCollection;
    }

    private function getEnabledMarketplaceTitles()
    {
        return $this->getEnabledMarketplacesCollection()->toOptionHash();
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current'=>true]);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/amazon_template/edit',
            [
                'id'   => $row->getData('template_id'),
                'type' => $row->getData('type'),
                'back' => 1
            ]
        );
    }

    //########################################
}
