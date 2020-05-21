<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\ControlPanel\Tabs\Database\Table;

use Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractGrid;
use \Ess\M2ePro\Model\ActiveRecord\Component\Parent\AbstractModel as ParentAbstractModel;

/**
 * Class Grid
 * @package Ess\M2ePro\Block\Adminhtml\ControlPanel\Tabs\Database\Table
 */
class Grid extends AbstractGrid
{
    const MERGE_MODE_COOKIE_KEY = 'database_tables_merge_mode_cookie_key';
    const MAX_COLUMN_VALUE_LENGTH = 255;

    private $cookieManager;

    /** @var  \Ess\M2ePro\Model\ControlPanel\Database\TableModel */
    private $tableModel;
    private $databaseTableFactory;

    //########################################

    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Ess\M2ePro\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->cookieManager = $cookieManager;
        $this->databaseTableFactory = $databaseTableFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelTable'.$this->getRequest()->getParam('table').'Grid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------

        $this->init();
    }

    private function init()
    {
        $tableName = $this->getRequest()->getParam('table');
        $component = $this->getRequest()->getParam('component');
        $mergeMode = $this->cookieManager->getCookie(self::MERGE_MODE_COOKIE_KEY);

        /** @var \Ess\M2ePro\Model\ControlPanel\Database\TableModel $model */
        $model = $this->databaseTableFactory->create(['data' => [
            'table_name' => $tableName,
            'merge_mode' => $mergeMode,
            'merge_mode_component' => $component
        ]]);

        $this->tableModel = $model;
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var  $collection */
        $collection = $this->tableModel->getModel()->getCollection();

        if ($this->tableModel->getTableName() == 'm2epro_operation_history') {
            $collection->getSelect()->columns([
                'total_run_time' => new \Zend_Db_Expr("TIME_TO_SEC(TIMEDIFF(`end_date`, `start_date`))")
            ]);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        foreach ($this->tableModel->getColumns() as $column) {
            $mergeMarkerHtml = '';
            if ($column['is_parent']) {
                $mergeMarkerHtml = '<span style="color: orangered;">[p]</span>';
            }
            if ($column['is_child']) {
                $mergeMarkerHtml = '<span style="color: forestgreen;">[ch]</span>';
            }

            $header = "<big>{$column['name']}</big>&nbsp;{$mergeMarkerHtml}<br>";
            $header .= "<small style=\"font-weight:normal;\">({$column['type']})</small>";

            $filterIndex = 'main_table.' . strtolower($column['name']);
            if ($column['is_child']) {
                $filterIndex = 'second_table.' . strtolower($column['name']);
            }

            $params = [
                'header'         => $header,
                'align'          => 'left',
                'type'           => $this->getColumnType($column),
                'string_limit'   => 65000,
                'index'          => strtolower($column['name']),
                'filter_index'   => $filterIndex,
                'frame_callback' => [$this, 'callbackColumnData'],

                'is_auto_increment' => strpos($column['extra'], 'increment') !== false,

                'is_parent' => $column['is_parent'],
                'is_child'  => $column['is_child'],
            ];

            if ($this->getColumnType($column) == 'datetime') {
                // will be replaced by UTC
                // vendor\magento\module-backend\Block\Widget\Grid\Column\Renderer\Datetime.php
                $params['timezone']    = false;
                $params['filter_time'] = true;
                $params['format']      = \IntlDateFormatter::MEDIUM;
                $params['filter']      = '\Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime';
            }

            if ($this->tableModel->getTableName() == 'm2epro_operation_history' && $column['name'] == 'nick') {
                $params['filter'] = '\Ess\M2ePro\Block\Adminhtml\ControlPanel\Tabs\Database\Table\Column\Filter\Select';
            }

            if ($this->tableModel->getTableName() == 'm2epro_operation_history' && $column['name'] == 'data') {
                $columnData = [
                    'header'                    => $this->__('Total Run Time'),
                    'align'                     => 'right',
                    'width'                     => '70px',
                    'type'                      => 'text',
                    'index'                     => 'total_run_time',
                    'filter'                    => '\Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Filter\Range',
                    'sortable'                  => true,
                    'frame_callback'            => [$this, 'callbackColumnTotalRunTime'],
                    'filter_condition_callback' => [$this, 'callbackTotalRunTimeFilter']
                ];

                $this->addColumn('total_time', $columnData);
            }

            $this->addColumn($column['name'], $params);
        }

        $this->addColumn('actions_row', [
            'header'    => '&nbsp;'.$this->__('Actions'),
            'align'     => 'left',
            'width'     => '70px',
            'type'      => 'text',
            'index'     => 'actions_row',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => [$this, 'callbackColumnActions']
        ]);

        return parent::_prepareColumns();
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $urlParams = [
            'table'     => urlencode($this->tableModel->getTableName()),
            'component' => urlencode($this->tableModel->getMergeModeComponent()),
            'merge'     => urlencode($this->tableModel->getIsMergeModeEnabled())
        ];

        $urls = [
            'controlPanel/deleteTableRows'        => $this->getUrl('*/*/deleteTableRows', $urlParams),
            'controlPanel/updateTableCells'       => $this->getUrl('*/*/updateTableCells', $urlParams),
            'controlPanel/addTableRow'            => $this->getUrl('*/*/addTableRow', $urlParams),
            'controlPanel/getTableCellsPopupHtml' => $this->getUrl('*/*/getTableCellsPopupHtml', $urlParams),

            'controlPanel/manageTable' => $this->getUrl(
                '*/*/manageTable',
                ['table' => $this->tableModel->getTableName()]
            ),
        ];
        $this->jsUrl->addUrls($urls);

        $key = self::MERGE_MODE_COOKIE_KEY;
        $this->js->addRequireJs([
            'jQuery' => 'jquery',
            'l'      => 'M2ePro/ControlPanel/Database/Grid'
        ], <<<JS

            window.ControlPanelDatabaseGridObj = new ControlPanelDatabaseGrid('{$this->getId()}');
            window.ControlPanelDatabaseGridObj.afterInitPage();
            window.ControlPanelDatabaseGridObj.setMergeModeCookieKey('{$key}');

            $$('div.main_cell_container.edit-allowed-class').each(function(el){
                el.observe('mouseover', ControlPanelDatabaseGridObj.mouseOverCell.bind(el));
                el.observe('mouseout', ControlPanelDatabaseGridObj.mouseOutCell.bind(el));
            });
JS
        );
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        // ---------------------------------------
        $this->getMassactionBlock()->addItem('deleteTableRows', [
             'label'    => $this->__('Delete'),
             'url'      => '',
        ]);
        // ---------------------------------------

        // ---------------------------------------
        $this->getMassactionBlock()->addItem('updateTableCells', [
            'label'    => $this->__('Update'),
            'url'      => ''
        ]);
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnData($value, $row, $column, $isExport)
    {
        $rowId = $row->getId();
        $columnId = $column->getId();
        $cellId = 'table_row_cell_'.$columnId.'_'.$rowId;

        if ($this->tableModel->getIsMergeModeEnabled() && $row instanceof ParentAbstractModel && $column['is_child']) {
            $value = $row->getChildObject()->getData($columnId);
        }

        $tempValue = '<span style="color:silver;"><small>NULL</small></span>';
        if ($value !== null) {
            $tempValue = $this->isColumnValueShouldBeCut($value) ? $this->cutColumnValue($value) : $value;
            $tempValue = $this->escapeHtml($tempValue);
        }

        $inputValue = 'NULL';
        if ($value !== null) {
            $inputValue = $this->escapeHtml($value);
        }

        $editAllowedClass = '';
        if (!$column->getData('is_auto_increment') && strlen($inputValue) < $column->getData('string_limit')) {
            $editAllowedClass = 'edit-allowed-class';
        }

        return <<<HTML
<div class="main_cell_container {$editAllowedClass}" style="min-height: 20px;" id="{$cellId}">

    <span id="{$cellId}_view_container">{$tempValue}</span>

    <span id="{$cellId}_edit_container" style="display: none;">
        <textarea style="width:100%; height:100%;" id="{$cellId}_edit_input"
                  onkeydown="ControlPanelDatabaseGridObj.onKeyDownEdit('{$rowId}','{$columnId}', event)"
>{$inputValue}</textarea>
    </span>

    <span id="{$cellId}_edit_link" style="display: none;">&nbsp;
        <a href="javascript:void(0);"
           onclick="ControlPanelDatabaseGridObj.switchCellToEdit('{$cellId}');">edit</a>
    </span>
    <span id="{$cellId}_view_link" style="display: none;">&nbsp;
        <a href="javascript:void(0);"
           onclick="ControlPanelDatabaseGridObj.switchCellToView('{$cellId}');">cancel</a>
    </span>
    <span id="{$cellId}_save_link" style="display: none;">&nbsp;
        <a href="javascript:void(0);"
           onclick="ControlPanelDatabaseGridObj.saveTableCell('{$rowId}','{$columnId}');">save</a>
    </span>
</div>
HTML;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $html = <<<HTML
<a href="javascript:void(0);" onclick="ControlPanelDatabaseGridObj.deleteTableRows('{$row->getId()}')">
    <span>delete</span>
</a>
HTML;

        if ($this->tableModel->getTableName() == 'm2epro_operation_history') {
            $urlUp = $this->getUrl(
                '*/*/showOperationHistoryExecutionTreeUp',
                ['operation_history_id' => $row->getId()]
            );
            $urlDown = $this->getUrl(
                '*/*/showOperationHistoryExecutionTreeDown',
                ['operation_history_id' => $row->getId()]
            );
            $html .= <<<HTML
<br/>
<a style="color: green;" href="{$urlUp}" target="_blank">
    <span>exec.&nbsp;tree&nbsp;&uarr;</span>
</a>
<br/>
<a style="color: green;" href="{$urlDown}" target="_blank">
    <span>exec.&nbsp;tree&nbsp;&darr;</span>
</a>
HTML;
        }
        $helper = $this->getHelper('Module_Database_Structure');
        $componentMode = $row->getData('component_mode');

        if (!$this->tableModel->getIsMergeModeEnabled() && $componentMode &&
            $helper->isTableHorizontalParent($this->tableModel->getTableName())) {
            $html .= <<<HTML
<br/>
<a style="color: green;" href="javascript:void(0);"
   onclick="ControlPanelDatabaseGridObj.mergeParentTable('{$componentMode}')">
    <span>join</span>
</a>
HTML;
        }

        return $html;
    }

    //########################################

    public function callbackColumnTotalRunTime($value, $row, $column, $isExport)
    {
        if (!is_numeric($value)) {
            return '<span style="color:silver;"><small>NULL</small></span>';
        }
        $color = $value > 1800 ? 'red' : 'green';
        $value = $this->escapeHtml($this->getTotalRunTimeForDisplay($value));

        return "<span style='color:$color;'>{$value}</span>";
    }

    /**
     * @param \Ess\M2ePro\Model\ResourceModel\OperationHistory\Collection $collection
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function callbackTotalRunTimeFilter($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value === null || !$value = preg_grep('/^\d+:\d{2}$/', $value)) {
            return $this;
        }

        $value = array_map(function ($item) {
            list($minutes, $seconds) = explode(':', $item);
            return (int) $minutes * 60 + $seconds;
        }, $value);

        if (isset($value['from'])) {
            $collection->getSelect()
                ->where("TIME_TO_SEC(TIMEDIFF(`end_date`, `start_date`)) >= {$value['from']}");
        }

        if (isset($value['to'])) {
            $collection->getSelect()
                ->where("TIME_TO_SEC(TIMEDIFF(`end_date`, `start_date`)) <= {$value['to']}");
        }

        return $this;
    }

    /**
     * @param $totalRunTime
     * @return null|string
     */
    protected function getTotalRunTimeForDisplay($totalRunTime)
    {
        $minutes = (int)($totalRunTime / 60);
        $minutes < 10 && $minutes = '0'.$minutes;

        $seconds = $totalRunTime - $minutes * 60;
        $seconds < 10 && $seconds = '0'.$seconds;

        return "{$minutes}:{$seconds}";
    }

    //########################################

    protected function isColumnValueShouldBeCut($originalValue)
    {
        if ($originalValue === null) {
            return false;
        }

        return strlen($originalValue) > self::MAX_COLUMN_VALUE_LENGTH;
    }

    protected function cutColumnValue($originalValue)
    {
        if ($originalValue === null) {
            return $originalValue;
        }

        return substr($originalValue, 0, self::MAX_COLUMN_VALUE_LENGTH) . ' ...';
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        if (!$this->getCollection()) {
            return $this;
        }

        if (!$column->getFilterConditionCallback()) {
            $value = $column->getFilter()->getValue();
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex()
                : $column->getIndex();

            if ($this->isNullFilter($value)) {
                $this->getCollection()->addFieldToFilter($field, ['null' => true]);
                return $this;
            }

            if ($this->isNotIsNullFilter($value)) {
                $this->getCollection()->addFieldToFilter($field, ['notnull' => true]);
                return $this;
            }

            if ($this->isUnEqualFilter($value)) {
                $this->getCollection()->addFieldToFilter($field, ['neq' => preg_replace('/^!=/', '', $value)]);
                return $this;
            }
        }

        return parent::_addColumnFilterToCollection($column);
    }

    private function isNullFilter($value)
    {
        if (is_string($value) && $value === 'isnull') {
            return true;
        }

        if (isset($value['from'], $value['to']) && $value['from'] === 'isnull' && $value['to'] === 'isnull') {
            return true;
        }

        return false;
    }

    private function isNotIsNullFilter($value)
    {
        if (is_string($value) && $value === '!isnull') {
            return true;
        }

        if (isset($value['from'], $value['to']) && $value['from'] === '!isnull' && $value['to'] === '!isnull') {
            return true;
        }

        return false;
    }

    private function isUnEqualFilter($value)
    {
        if (is_string($value) && strpos($value, '!=') === 0) {
            return true;
        }

        return false;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/databaseTableGrid', ['_current'=>true]);
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getTableModel()
    {
        return $this->tableModel;
    }

    //########################################

    private function getColumnType($columnData)
    {
        if ($columnData['type'] == 'datetime') {
            return 'datetime';
        }

        if (preg_match('/int|float|decimal/', $columnData['type'])) {
            return 'number';
        }

        return 'text';
    }

    //########################################
}
