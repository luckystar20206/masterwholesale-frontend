<?php
/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Helper\Module;

/**
 * Class Log
 * @package Ess\M2ePro\Helper\Module
 */
class Log extends \Ess\M2ePro\Helper\AbstractHelper
{
    //########################################

    /**
     * @param string $string
     * @param array $params
     * @param array $links
     * @return string
     */
    public function encodeDescription($string, array $params = [], array $links = [])
    {
        if (empty($params) && empty($links)) {
            return $string;
        }

        $descriptionData = [
            'string' => $string,
            'params' => $params,
            'links'  => $links
        ];

        return $this->getHelper('Data')->jsonEncode($descriptionData);
    }

    /**
     * @param string $string
     * @return string
     */
    public function decodeDescription($string)
    {
        if (!is_string($string) || $string == '') {
            return '';
        }

        if ($string{0} != '{') {
            return $this->getHelper('Module\Translation')->__($string);
        }

        $descriptionData = $this->getHelper('Data')->jsonDecode($string);
        $string = $this->getHelper('Module\Translation')->__($descriptionData['string']);

        if (!empty($descriptionData['params'])) {
            $string = $this->addPlaceholdersToMessage($string, $descriptionData['params']);
        }

        if (!empty($descriptionData['links'])) {
            $string = $this->addLinksToMessage($string, $descriptionData['links']);
        }

        return $string;
    }

    // ---------------------------------------

    protected function addPlaceholdersToMessage($string, $params)
    {
        foreach ($params as $key => $value) {
            if (isset($value{0}) && $value{0} == '{') {
                $tempValueArray = $this->getHelper('Data')->jsonDecode($value);
                is_array($tempValueArray) && $value = $this->decodeDescription($value);
            }

            if ($key{0} == '!') {
                $key = substr($key, 1);
            } else {
                $value = $this->getHelper('Module\Translation')->__($value);
            }

            $string = str_replace('%'.$key.'%', $value, $string);
        }

        return $string;
    }

    protected function addLinksToMessage($string, $links)
    {
        $readMoreLinks = [];
        $resultString = $string;

        foreach ($links as $link) {
            preg_match('/!\w*_start!/', $resultString, $foundedStartMatches);

            if (empty($foundedStartMatches)) {
                $readMoreLinks[] = $link;
                continue;
            } else {
                $startPart = $foundedStartMatches[0];
                $endPart = str_replace('start', 'end', $startPart);

                $wasFoundEndMatches = strpos($resultString, $endPart);

                if ($wasFoundEndMatches !== false) {
                    $openLinkTag = '<a href="' . $link . '" target="_blank">';
                    $closeLinkTag = '</a>';

                    $resultString = str_replace($startPart, $openLinkTag, $resultString);
                    $resultString = str_replace($endPart, $closeLinkTag, $resultString);
                } else {
                    $readMoreLinks[] = $link;
                }
            }
        }

        if (!empty($readMoreLinks)) {
            $translation = $this->getHelper('Module\Translation');

            foreach ($readMoreLinks as &$link) {
                $link = '<a href="' . $link . '" target="_blank">' . $translation->__('here') . '</a>';
            }

            $delimiter = $translation->__('or');
            $readMoreString = $translation->__('Details').' '.implode(' '.$delimiter.' ', $readMoreLinks).'.';

            $resultString .= ' ' . $readMoreString;
        }

        return $resultString;
    }

    // ---------------------------------------

    public function getActionTitleByClass($class, $type)
    {
        $reflectionClass = new \ReflectionClass($class);
        $tempConstants = $reflectionClass->getConstants();

        foreach ($tempConstants as $key => $value) {
            if ($key == '_'.$type) {
                return $this->getHelper('Module\Translation')->__($key);
            }
        }

        return '';
    }

    public function getActionsTitlesByClass($class)
    {
        switch ($class) {
            case 'Ess\M2ePro\Model\Listing\Log':
            case 'Ess\M2ePro\Model\Listing\Other\Log':
            case 'Ess\M2ePro\Model\Ebay\Account\PickupStore\Log':
                $prefix = 'ACTION_';
                break;

            case 'Ess\M2ePro\Model\Synchronization\Log':
                $prefix = 'TASK_';
                break;
        }

        $reflectionClass = new \ReflectionClass($class);
        $tempConstants = $reflectionClass->getConstants();

        $actionsNames = [];
        foreach ($tempConstants as $key => $value) {
            if (substr($key, 0, strlen($prefix)) == $prefix) {
                $actionsNames[$key] = $value;
            }
        }

        $actionsValues = [];
        foreach ($actionsNames as $action => $valueAction) {
            foreach ($tempConstants as $key => $valueConstant) {
                if ($key == '_'.$action) {
                    $actionsValues[$valueAction] = $this->helperFactory
                        ->getObject('Module\Translation')->__($valueConstant);
                }
            }
        }

        return $actionsValues;
    }

    public function getStatusByResultType($resultType)
    {
        $typesStatusesMap = [
            \Ess\M2ePro\Model\Log\AbstractModel::TYPE_NOTICE  => \Ess\M2ePro\Helper\Data::STATUS_SUCCESS,
            \Ess\M2ePro\Model\Log\AbstractModel::TYPE_SUCCESS => \Ess\M2ePro\Helper\Data::STATUS_SUCCESS,
            \Ess\M2ePro\Model\Log\AbstractModel::TYPE_WARNING => \Ess\M2ePro\Helper\Data::STATUS_WARNING,
            \Ess\M2ePro\Model\Log\AbstractModel::TYPE_ERROR   => \Ess\M2ePro\Helper\Data::STATUS_ERROR,
        ];

        return $typesStatusesMap[$resultType];
    }

    //########################################
}
