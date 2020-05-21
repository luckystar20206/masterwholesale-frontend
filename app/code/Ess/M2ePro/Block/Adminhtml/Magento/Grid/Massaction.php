<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Magento\Grid;

/**
 * Class Massaction
 * @package Ess\M2ePro\Block\Adminhtml\Magento\Grid
 */
class Massaction extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{
    protected $_groups      = [];

    //########################################

    public function isAvailable()
    {
        // also return available if need to display advanced filters, but hide massactions
        return ($this->getCount() > 0 && $this->getParentBlock()->getMassactionIdField())
        || (isset($this->getParentBlock()->hideMassactionColumn) && $this->getParentBlock()->hideMassactionColumn);
    }

    public function setGroups(array $groups)
    {
        foreach ($groups as $groupName => $label) {
            $this->_groups[$groupName] = [
                'label' => $label,
                'items' => []
            ];
        }

        return $this;
    }

    public function addItem($itemId, array $item, $group = null)
    {
        if (!empty($group) && isset($this->_groups[$group])) {
            $this->_groups[$group]['items'][] = $itemId;
        }

        return parent::addItem($itemId, $item);
    }

    // ---------------------------------------

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        return $this->injectOptGroupsIfNeed($html);
    }

    public function getJavaScript()
    {
        // checking if need to remove massactions, but need to display advanced filters
        if (!isset($this->getParentBlock()->hideMassactionColumn) || !$this->getParentBlock()->hideMassactionColumn) {
            $javascript = parent::getJavaScript();

            return $javascript . <<<HTML
window['{$this->getJsObjectName()}'] = {$this->getJsObjectName()};
HTML;
        }
        return '';
    }

    //########################################

    protected function injectOptGroupsIfNeed($html)
    {
        if (empty($this->_groups)) {
            return $html;
        }

        $selectId    = $this->getHtmlId() . '-select';
        $selectClass = 'required-entry local-validation admin__control-select';
        $pattern     = '/(<select\s*id="'.$selectId.'"\s*class="'.$selectClass.'"[^<]*>.*?<\/select>)/si';

        if (!preg_match($pattern, $html, $matches)) {
            return $html;
        }

        return preg_replace($pattern, $this->wrapOptionsInOptGroups($matches[1]), $html);
    }

    // ---------------------------------------

    public function wrapOptionsInOptGroups($html)
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $xpathObj = new \DOMXPath($dom);
        $select = $dom->getElementsByTagName('select')->item(0);

        foreach ($this->_groups as $groupName => $groupData) {
            if (count($groupData['items']) == 0) {
                continue;
            }

            $optgroup = $dom->createElement('optgroup');
            $optgroup->setAttribute('label', $groupData['label']);

            foreach ($groupData['items'] as $itemId) {
                $option = $xpathObj->query("//select/option[@value='{$itemId}']", $select)
                                   ->item(0);
                $option = $select->removeChild($option);
                $optgroup->appendChild($option);
            }

            $select->appendChild($optgroup);
        }

        // Moving remaining options in end of list
        foreach ($xpathObj->query('//select/option', $select) as $option) {
            if (empty($option->getAttribute('value'))) {
                continue;
            }

            $option = $select->removeChild($option);
            $select->appendChild($option);
        }

        // Removing doctype, html, body
        $dom->removeChild($dom->doctype);
        $dom->replaceChild($dom->firstChild->firstChild->firstChild, $dom->firstChild);

        libxml_use_internal_errors(false);
        return $dom->saveHTML();
    }

    //########################################

    /**
     * Method is overwritten due to Magento issue (it is impossible to select all items in grid)
     * Magento feature which allows to getGridIds() by custom massAction column (getMassactionIdField) is not supported
     * @return string
     */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        /** @var \Magento\Framework\Data\Collection $allIdsCollection */
        $allIdsCollection = clone $this->getParentBlock()->getCollection();
        $gridIds = $allIdsCollection->clear()->setPageSize(0)->getAllIds();

        if (!empty($gridIds)) {
            return join(",", $gridIds);
        }
        return '';
    }

    //########################################
}
