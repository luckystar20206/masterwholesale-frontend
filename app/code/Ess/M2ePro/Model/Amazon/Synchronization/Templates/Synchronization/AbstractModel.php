<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Synchronization\Templates\Synchronization;

/**
 * Class AbstractModel
 * @package Ess\M2ePro\Model\Amazon\Synchronization\Templates\Synchronization
 */
abstract class AbstractModel extends \Ess\M2ePro\Model\Amazon\Synchronization\Templates\AbstractModel
{
    protected $resourceConnection;

    protected $amazonFactory;
    /**
     * @var \Ess\M2ePro\Model\Synchronization\Templates\Synchronization\Runner
     */
    protected $runner = null;

    /**
     * @var \Ess\M2ePro\Model\Amazon\Synchronization\Templates\Synchronization\Inspector
     */
    protected $inspector = null;

    /**
     * @var \Ess\M2ePro\Model\Synchronization\Templates\ProductChanges\Manager
     */
    protected $productChangesManager = null;

    //########################################

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->amazonFactory = $amazonFactory;
        parent::__construct($amazonFactory, $activeRecordFactory, $helperFactory, $modelFactory);
    }

    //########################################

    protected function processTask($taskPath)
    {
        return parent::processTask('Synchronization\\'.$taskPath);
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Synchronization\Templates\Synchronization\Runner $object
     */
    public function setRunner(\Ess\M2ePro\Model\Synchronization\Templates\Synchronization\Runner $object)
    {
        $this->runner = $object;
    }

    /**
     * @return \Ess\M2ePro\Model\Synchronization\Templates\Synchronization\Runner
     */
    public function getRunner()
    {
        return $this->runner;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Amazon\Synchronization\Templates\Synchronization\Inspector $object
     */
    public function setInspector(\Ess\M2ePro\Model\Amazon\Synchronization\Templates\Synchronization\Inspector $object)
    {
        $this->inspector = $object;
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Synchronization\Templates\Synchronization\Inspector
     */
    public function getInspector()
    {
        return $this->inspector;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Synchronization\Templates\ProductChanges\Manager $object
     */
    public function setProductChangesManager(\Ess\M2ePro\Model\Synchronization\Templates\ProductChanges\Manager $object)
    {
        $this->productChangesManager = $object;
    }

    /**
     * @return \Ess\M2ePro\Model\Synchronization\Templates\ProductChanges\Manager
     */
    public function getProductChangesManager()
    {
        return $this->productChangesManager;
    }

    //########################################
}
