<?php
namespace Smartwave\Dailydeals\Controller\Adminhtml;

abstract class Dailydeal extends \Magento\Backend\App\Action
{
    /**
     * Dailydeal Factory
     *
     * @var \Smartwave\Dailydeals\Model\DailydealFactory
     */
    protected $dailydealFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Result redirect factory
     *
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * constructor
     *
     * @param \Smartwave\Dailydeals\Model\DailydealFactory $dailydealFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Smartwave\Dailydeals\Model\DailydealFactory $dailydealFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->dailydealFactory      = $dailydealFactory;
        $this->coreRegistry          = $coreRegistry;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($context);
    }

    /**
     * Init Dailydeal
     *
     * @return \Smartwave\Dailydeals\Model\Dailydeal
     */
    protected function initDailydeal()
    {
        $dailydealId  = (int) $this->getRequest()->getParam('dailydeal_id');
        /** @var \Smartwave\Dailydeals\Model\Dailydeal $dailydeal */
        $dailydeal    = $this->dailydealFactory->create();
        if ($dailydealId) {
            $dailydeal->load($dailydealId);
        }
        $this->coreRegistry->register('sw_dailydeals_dailydeal', $dailydeal);
        return $dailydeal;
    }
}
