<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Servicing;

/**
 * Class Task
 * @package Ess\M2ePro\Model\Servicing
 */
abstract class Task extends \Ess\M2ePro\Model\AbstractModel
{
    private $params = [];
    private $initiator;

    protected $config;
    protected $cacheConfig;
    protected $storeManager;
    protected $parentFactory;
    protected $activeRecordFactory;
    protected $resource;

    //########################################

    public function __construct(
        \Magento\Eav\Model\Config $config,
        \Ess\M2ePro\Model\Config\Manager\Cache $cacheConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Factory $parentFactory
    ) {
        $this->config = $config;
        $this->cacheConfig = $cacheConfig;
        $this->storeManager = $storeManager;
        $this->activeRecordFactory = $activeRecordFactory;
        $this->parentFactory = $parentFactory;
        $this->resource = $resource;

        parent::__construct($helperFactory, $modelFactory);
    }

    //########################################

    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;
        return $this;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    //########################################

    /**
     * @return string
     */
    abstract public function getPublicNick();

    //########################################

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params = [])
    {
        $this->params = $params;
    }

    //########################################

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return true;
    }

    // ---------------------------------------
    /**
     * @return array
     */
    abstract public function getRequestData();

    /**
     * @param array $data
     * @return null
     */
    abstract public function processResponseData(array $data);

    //########################################
}
