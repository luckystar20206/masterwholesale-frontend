<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Walmart\Account;

/**
 * Class Grid
 * @package Ess\M2ePro\Block\Adminhtml\Walmart\Account
 */
class Grid extends \Ess\M2ePro\Block\Adminhtml\Account\Grid
{
    protected $WalmartFactory;

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Walmart\Factory $WalmartFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->WalmartFactory = $WalmartFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = $this->WalmartFactory->getObject('Account')->getCollection();

        $collection->getSelect()->joinLeft(
            [
            'm' => $this->activeRecordFactory->getObject('Marketplace')->getResource()->getMainTable(),
            ],
            '(`m`.`id` = `second_table`.`marketplace_id`)',
            ['marketplace_title'=>'title']
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header'    => $this->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'id',
            'filter_index' => 'main_table.id'
        ]);

        $this->addColumn('title', [
            'header'    => $this->__('Title / Info'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'title',
            'escape'    => true,
            'filter_index' => 'main_table.title',
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle']
        ]);

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $marketplaceLabel = $this->__('Marketplace');
        $marketplaceTitle = $row->getData('marketplace_title');

        $consumerLabel = $this->__('Consumer ID');
        $consumerId = $row->getChildObject()->getData('consumer_id');

        $value = <<<HTML
            <div>
                {$value}<br/>
                <span style="font-weight: bold">{$consumerLabel}</span>:
                <span style="color: #505050">{$consumerId}</span>
                <br/>
                <span style="font-weight: bold">{$marketplaceLabel}</span>:
                <span style="color: #505050">{$marketplaceTitle}</span>
                <br/>
            </div>
HTML;

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
            'main_table.title LIKE ?
            OR m.title LIKE ?
            OR second_table.consumer_id LIKE ?',
            '%'. $value .'%'
        );
    }

    //########################################
}
