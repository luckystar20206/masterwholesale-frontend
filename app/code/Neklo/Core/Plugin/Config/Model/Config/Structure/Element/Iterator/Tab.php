<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Plugin\Config\Model\Config\Structure\Element\Iterator;

class Tab
{
    /**
     * @param \Magento\Config\Model\Config\Structure\Element\Iterator\Tab $subject
     * @param array $elements
     * @param $scope
     *
     * @return array
     */
    public function beforeSetElements(\Magento\Config\Model\Config\Structure\Element\Iterator\Tab $subject, array $elements, $scope)
    {
        $children = [];
        foreach ($elements as $elementName => $element) {
            if ($element['id'] !== 'neklo') {
                continue;
            }

            $sectionList = $element['children'];
            usort($sectionList, [$this, '_sort']);
            foreach ($sectionList as $section) {
                $children[$section['id']] = $section;
            }
            $elements[$elementName]['children'] = $children;
            break;
        }

        return [$elements, $scope];
    }

    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    private function _sort($a, $b)
    {
        if ($a['id'] == 'neklo_core') {
            return 1;
        }
        if ($b['id'] == 'neklo_core') {
            return -1;
        }
        return strcasecmp($a['label'], $b['label']);
    }
}
