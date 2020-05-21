<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Block;

use Magento\Cms\Block\Block;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Osc\Helper\Data as OscHelper;
use Mageplaza\Osc\Model\System\Config\Source\StaticBlockPosition as Position;
use Zend_Serializer_Exception;

/**
 * Class StaticBlock
 * @package Mageplaza\Osc\Block
 */
class StaticBlock extends Template
{
    /**
     * @var OscHelper
     */
    private $_oscHelper;

    /**
     * StaticBlock constructor.
     *
     * @param Context $context
     * @param OscHelper $oscHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        OscHelper $oscHelper,
        array $data = []
    ) {
        $this->_oscHelper = $oscHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws Zend_Serializer_Exception
     */
    public function getStaticBlock()
    {
        try {
            $layout = $this->getLayout();
        } catch (LocalizedException $e) {
            $this->_logger->critical($e->getMessage());

            return [];
        }

        $result = [];

        $config = $this->_oscHelper->isEnableStaticBlock() ? $this->_oscHelper->getStaticBlockList() : [];
        foreach ($config as $key => $row) {
            /** @var Block $block */
            $block    = $layout->createBlock(Block::class)->setBlockId($row['block'])->toHtml();
            $name     = $this->getNameInLayout();
            $position = (int) $row['position'];

            if (($position === Position::SHOW_IN_SUCCESS_PAGE && $name === 'osc.static-block.success')
                || ($position === Position::SHOW_AT_TOP_CHECKOUT_PAGE && $name === 'osc.static-block.top')
                || ($position === Position::SHOW_AT_BOTTOM_CHECKOUT_PAGE && $name === 'osc.static-block.bottom')) {
                $result[] = [
                    'content'   => $block,
                    'sortOrder' => $row['sort_order']
                ];
            }
        }

        usort($result, function ($a, $b) {
            return ($a['sortOrder'] <= $b['sortOrder']) ? -1 : 1;
        });

        return $result;
    }
}
