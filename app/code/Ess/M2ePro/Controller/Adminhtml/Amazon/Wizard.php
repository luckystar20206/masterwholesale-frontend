<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon;

use Ess\M2ePro\Controller\Adminhtml\Context;

/**
 * Class Wizard
 * @package Ess\M2ePro\Controller\Adminhtml\Amazon
 */
abstract class Wizard extends \Ess\M2ePro\Controller\Adminhtml\Wizard
{
    protected $amazonFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        Context $context
    ) {
        $this->amazonFactory = $amazonFactory;
        parent::__construct($nameBuilder, $context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ess_M2ePro::amazon');
    }

    //########################################

    protected function getCustomViewNick()
    {
        return \Ess\M2ePro\Helper\View\Amazon::NICK;
    }

    protected function getMenuRootNodeNick()
    {
        return \Ess\M2ePro\Helper\View\Amazon::MENU_ROOT_NODE_NICK;
    }

    protected function initResultPage()
    {
        if ($this->resultPage !== null) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()->setActiveMenu($this->getMenuRootNodeNick());
    }

    protected function getMenuRootNodeLabel()
    {
        return $this->getHelper('View\Amazon')->getMenuRootNodeLabel();
    }

    //########################################

    protected function indexAction()
    {
        if ($this->isSkipped()) {
            return $this->_redirect('*/amazon_listing/index/');
        }

        return parent::indexAction();
    }

    //########################################
}
