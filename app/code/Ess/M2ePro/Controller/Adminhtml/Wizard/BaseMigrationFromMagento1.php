<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard;

use \Magento\Backend\App\Action;

/**
 * Class BaseMigrationFromMagento1
 * @package Ess\M2ePro\Controller\Adminhtml\Wizard
 */
abstract class BaseMigrationFromMagento1 extends Action
{
    const WIZARD_STATUS_CONFIG_PATH = 'm2epro/migrationFromMagento1/status';

    const WIZARD_STATUS_PREPARED    = 'prepared';
    const WIZARD_STATUS_IN_PROGRESS = 'in_progress';
    const WIZARD_STATUS_COMPLETED   = 'completed';

    protected $currentWizardStep = null;

    /** @var \Magento\Framework\App\ResourceConnection|null  */
    protected $resourceConnection = null;

    /** @var \Magento\Framework\View\Result\PageFactory $resultPageFactory  */
    protected $resultPageFactory = null;

    /** @var \Ess\M2ePro\Helper\Factory $helperFactory */
    protected $helperFactory = null;

    //########################################

    public function __construct(\Ess\M2ePro\Controller\Adminhtml\Context $context)
    {
        $this->resourceConnection = $context->getResourceConnection();
        $this->resultPageFactory = $context->getResultPageFactory();
        $this->helperFactory = $context->getHelperFactory();

        parent::__construct($context);
    }

    //########################################

    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if ((
            $this->getCurrentWizardStatus() === self::WIZARD_STATUS_PREPARED ||
            $this->getCurrentWizardStatus() === self::WIZARD_STATUS_IN_PROGRESS
        ) &&
            $this->getRequest()->getActionName() != 'database'
        ) {
            return $this->_redirect('*/wizard_migrationFromMagento1/database');
        }

        if ($this->getRequest()->getActionName() == 'database' && (
                $this->getCurrentWizardStatus() !== self::WIZARD_STATUS_PREPARED &&
                $this->getCurrentWizardStatus() !== self::WIZARD_STATUS_IN_PROGRESS
            )
        ) {
            return $this->_redirect('*/wizard_migrationFromMagento1/disableModule');
        }

        return parent::dispatch($request);
    }

    //########################################

    protected function _isAllowed()
    {
        return $this->_auth->isLoggedIn();
    }

    //########################################

    protected function getCurrentWizardStatus()
    {
        if ($this->currentWizardStep === null) {
            $select = $this->resourceConnection->getConnection()
                ->select()
                ->from(
                    $this->helperFactory->getObject('Module_Database_Structure')
                        ->getTableNameWithPrefix('core_config_data'),
                    'value'
                )
                ->where('scope = ?', 'default')
                ->where('scope_id = ?', 0)
                ->where('path = ?', self::WIZARD_STATUS_CONFIG_PATH);

            $this->currentWizardStep = $this->resourceConnection->getConnection()->fetchOne($select);
        }

        return $this->currentWizardStep;
    }

    //########################################
}
