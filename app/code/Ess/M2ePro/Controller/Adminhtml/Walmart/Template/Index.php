<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Walmart\Template;

use Ess\M2ePro\Controller\Adminhtml\Walmart\Template;

/**
 * Class Index
 * @package Ess\M2ePro\Controller\Adminhtml\Walmart\Template
 */
class Index extends Template
{
    //########################################

    public function execute()
    {
        $content = $this->createBlock(
            'Walmart\\Template'
        );

        $this->getResultPage()->getConfig()->getTitle()->prepend('Policies');
        $this->addContent($content);
        $this->setPageHelpLink('x/QwBhAQ');

        return $this->getResultPage();
    }

    //########################################
}
