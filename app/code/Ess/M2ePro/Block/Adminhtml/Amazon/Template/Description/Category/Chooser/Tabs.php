<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Template\Description\Category\Chooser;

/**
 * Class Tabs
 * @package Ess\M2ePro\Block\Adminhtml\Amazon\Template\Description\Category\Chooser
 */
class Tabs extends \Ess\M2ePro\Block\Adminhtml\Magento\Tabs\AbstractHorizontalTabs
{
    //########################################

    const TAB_ID_RECENT = 'recent';
    const TAB_ID_BROWSE = 'browse';
    const TAB_ID_SEARCH = 'search';

    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('amazonTemplateDescriptionCategoryChooserTabs');
        $this->setDestElementId('chooser_tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        !$this->isNeedToHideRecent() && $this->addTab(self::TAB_ID_RECENT, [
            'label'   => $this->__('Recently Used'),
            'title'   => $this->__('Recently Used'),
            'content' => $this->createBlock(
                'Amazon_Template_Description_Category_Chooser_Tabs_Recent'
            )->toHtml(),
            'active'  => true
        ]);

        $this->addTab(self::TAB_ID_BROWSE, [
            'label'   => $this->__('Browse'),
            'title'   => $this->__('Browse'),
            'content' => $this->createBlock(
                'Amazon_Template_Description_Category_Chooser_Tabs_Browse'
            )->toHtml(),
            'active'  => $this->isNeedToHideRecent() ? true : false
        ]);

        $this->addTab(self::TAB_ID_SEARCH, [
            'label'   => $this->__('Search'),
            'title'   => $this->__('Search'),
            'content' => $this->createBlock(
                'Amazon_Template_Description_Category_Chooser_Tabs_Search'
            )->toHtml()
        ]);

        return parent::_prepareLayout();
    }

    //########################################

    public function isNeedToHideRecent()
    {
        return $this->getHelper('Data\GlobalData')->getValue('category_chooser_hide_recent', true);
    }

    //########################################
}
