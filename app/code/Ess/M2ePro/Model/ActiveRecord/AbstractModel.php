<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ActiveRecord;

/**
 * Class AbstractModel
 * @package Ess\M2ePro\Model\ActiveRecord
 */
abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel
{
    //########################################

    protected $cacheLoading = false;
    protected $isObjectCreatingState = false;

    protected $modelFactory;
    protected $activeRecordFactory;
    protected $helperFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->modelFactory = $modelFactory;
        $this->activeRecordFactory = $activeRecordFactory;
        $this->helperFactory = $helperFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    //########################################

    public function isObjectCreatingState($value = null)
    {
        if ($value === null) {
            return $this->isObjectCreatingState;
        }

        $this->isObjectCreatingState = $value;
        return $this->isObjectCreatingState;
    }

    //########################################

    public function getObjectModelName()
    {
        $className = $this->getHelper('Client')->getClassName($this);
        return str_replace('Ess\M2ePro\Model\\', '', $className);
    }

    //########################################

    /**
     * @param int $modelId
     * @param null|string $field
     * @return \Ess\M2ePro\Model\ActiveRecord\AbstractModel
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function load($modelId, $field = null)
    {
        parent::load($modelId, $field);

        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic(
                'Instance does not exist.',
                [
                    'id'    => $modelId,
                    'field' => $field,
                    'model' => $this->_resourceName
                ]
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        if ($this->getId() !== null && $this->isCacheEnabled()) {
            $this->getHelper('Data_Cache_Permanent')->removeTagValues($this->getCacheInstancesTag());
        }

        if ($this->isObjectNew()) {
            $this->isObjectCreatingState(true);
        }

        $result = parent::save();

        $this->isObjectCreatingState(false);
        return $result;
    }

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function delete()
    {
        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Method require loaded instance first');
        }

        if ($this->isLocked()) {
            return false;
        }

        if ($this->isCacheEnabled()) {
            $this->getHelper('Data_Cache_Permanent')->removeTagValues($this->getCacheInstancesTag());
        }

        $this->deleteProcessingLocks();
        return parent::delete();
    }

    //########################################

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function isLocked()
    {
        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Method require loaded instance first');
        }

        if ($this->isSetProcessingLock(null)) {
            return true;
        }

        return false;
    }

    public function deleteProcessings()
    {
        $processingIds = [];
        foreach ($this->getProcessingLocks() as $processingLock) {
            $processingIds[] = $processingLock->getProcessingId();
        }

        /** @var $collection \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel */
        $collection = $this->activeRecordFactory->getObject('Processing')->getCollection();
        $collection->addFieldToFilter('id', ['in'=>array_unique($processingIds)]);

        foreach ($collection->getItems() as $processing) {
            /** @var $processing \Ess\M2ePro\Model\Processing */

            /** @var \Ess\M2ePro\Model\Processing\Runner $processingRunner */
            $processingRunner = $this->modelFactory->getObject($processing->getModel());
            $processingRunner->setProcessingObject($processing);

            $processingRunner->complete();
        }
    }

    // ---------------------------------------

    public function addProcessingLock($tag = null, $processingId = null)
    {
        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Method require loaded instance first');
        }

        if ($this->isSetProcessingLock($tag, $processingId)) {
            return;
        }

        /** @var \Ess\M2ePro\Model\Processing\Lock $model */
        $model = $this->activeRecordFactory->getObject('Processing\Lock');

        $dataForAdd = [
            'processing_id' => $processingId,
            'model_name'    => $this->getObjectModelName(),
            'object_id'     => $this->getId(),
            'tag'           => $tag,
        ];

        $model->setData($dataForAdd)->save();
    }

    public function deleteProcessingLocks($tag = false, $processingId = false)
    {
        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Method require loaded instance first');
        }

        foreach ($this->getProcessingLocks($tag, $processingId) as $processingLock) {
            $processingLock->delete();
        }
    }

    // ---------------------------------------

    public function isSetProcessingLock($tag = false, $processingId = false)
    {
        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Method require loaded instance first');
        }

        return !empty($this->getProcessingLocks($tag, $processingId));
    }

    /**
     * @param bool|false $tag
     * @param bool|false $processingId
     * @return \Ess\M2ePro\Model\Processing\Lock[]
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getProcessingLocks($tag = false, $processingId = false)
    {
        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Method require loaded instance first');
        }

        /** @var $collection \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel */
        $lockedCollection = $this->activeRecordFactory->getObject('Processing\Lock')->getCollection();

        $lockedCollection->addFieldToFilter('model_name', $this->getObjectModelName());
        $lockedCollection->addFieldToFilter('object_id', $this->getId());

        $tag === null && $tag = ['null'=>true];
        $tag !== false && $lockedCollection->addFieldToFilter('tag', $tag);
        $processingId !== false && $lockedCollection->addFieldToFilter('processing_id', $processingId);

        return $lockedCollection->getItems();
    }

    //########################################

    /**
     * @param string $modelName
     * @param string $fieldName
     * @param bool $asObjects
     * @param array $filters
     * @param array $sort
     * @return array|\Ess\M2ePro\Model\ActiveRecord\AbstractModel[]
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function getRelatedSimpleItems(
        $modelName,
        $fieldName,
        $asObjects = false,
        array $filters = [],
        array $sort = []
    ) {
        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Method require loaded instance first');
        }

        $tempModel = $this->activeRecordFactory->getObject($modelName);

        if ($tempModel === null || !($tempModel instanceof \Ess\M2ePro\Model\ActiveRecord\AbstractModel)) {
            return [];
        }

        return $this->getRelatedItems($tempModel, $fieldName, $asObjects, $filters, $sort);
    }

    /**
     * @param \Ess\M2ePro\Model\ActiveRecord\AbstractModel $model
     * @param string $fieldName
     * @param bool $asObjects
     * @param array $filters
     * @param array $sort
     * @return array|\Ess\M2ePro\Model\ActiveRecord\AbstractModel[]
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function getRelatedItems(
        \Ess\M2ePro\Model\ActiveRecord\AbstractModel $model,
        $fieldName,
        $asObjects = false,
        array $filters = [],
        array $sort = []
    ) {
        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Method require loaded instance first');
        }

        /** @var $tempCollection \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection */
        $tempCollection = $model->getCollection();
        $tempCollection->addFieldToFilter(new \Zend_Db_Expr("`{$fieldName}`"), $this->getId());

        foreach ($filters as $field => $filter) {
            if ($filter instanceof \Zend_Db_Expr) {
                $tempCollection->getSelect()->where((string)$filter);
                continue;
            }

            $tempCollection->addFieldToFilter(new \Zend_Db_Expr("`{$field}`"), $filter);
        }

        foreach ($sort as $field => $order) {
            $order = strtoupper(trim($order));
            if ($order != \Magento\Framework\Data\Collection::SORT_ORDER_ASC &&
                $order != \Magento\Framework\Data\Collection::SORT_ORDER_DESC) {
                continue;
            }
            $tempCollection->setOrder($field, $order);
        }

        if ((bool)$asObjects) {
            return $tempCollection->getItems();
        }

        $tempArray = $tempCollection->toArray();
        return $tempArray['items'];
    }

    //########################################

    /**
     * @param string $fieldName
     *
     * @return array
     *
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getSettings($fieldName)
    {
        $settings = $this->getData((string)$fieldName);

        if ($settings === null) {
            return [];
        }

        $settings = $this->getHelper('Data')->jsonDecode($settings);
        return !empty($settings) ? $settings : [];
    }

    /**
     * @param string       $fieldName
     * @param string|array $settingNamePath
     * @param mixed        $defaultValue
     *
     * @return mixed|null
     */
    public function getSetting(
        $fieldName,
        $settingNamePath,
        $defaultValue = null
    ) {
        if (empty($settingNamePath)) {
            return $defaultValue;
        }

        $settings = $this->getSettings($fieldName);

        !is_array($settingNamePath) && $settingNamePath = [$settingNamePath];

        foreach ($settingNamePath as $pathPart) {
            if (!isset($settings[$pathPart])) {
                return $defaultValue;
            }

            $settings = $settings[$pathPart];
        }

        if (is_numeric($settings)) {
            $settings = ctype_digit($settings) ? (int)$settings : $settings;
        }

        return $settings;
    }

    // ---------------------------------------

    /**
     * @param string $fieldName
     * @param array  $settings
     *
     * @return \Ess\M2ePro\Model\ActiveRecord\AbstractModel
     *
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function setSettings($fieldName, array $settings = [])
    {
        $this->setData((string)$fieldName, $this->getHelper('Data')->jsonEncode($settings));

        return $this;
    }

    /**
     * @param string       $fieldName
     * @param string|array $settingNamePath
     * @param mixed        $settingValue
     *
     * @return \Ess\M2ePro\Model\ActiveRecord\AbstractModel
     */
    public function setSetting(
        $fieldName,
        $settingNamePath,
        $settingValue
    ) {
        if (empty($settingNamePath)) {
            return $this;
        }

        $settings = $this->getSettings($fieldName);
        $target = &$settings;

        !is_array($settingNamePath) && $settingNamePath = [$settingNamePath];

        $currentPathNumber = 0;
        $totalPartsNumber = count($settingNamePath);

        foreach ($settingNamePath as $pathPart) {
            $currentPathNumber++;

            if (!array_key_exists($pathPart, $settings) && $currentPathNumber != $totalPartsNumber) {
                $target[$pathPart] = [];
            }

            if ($currentPathNumber != $totalPartsNumber) {
                $target = &$target[$pathPart];
                continue;
            }

            $target[$pathPart] = $settingValue;
        }

        $this->setSettings($fieldName, $settings);

        return $this;
    }

    //########################################

    public function getDataSnapshot()
    {
        $data = $this->getData();

        foreach ($data as &$value) {
            $value !== null && !is_array($value) && $value = (string)$value;
        }

        return $data;
    }

    //########################################

    /**
     * @return boolean
     */
    public function isCacheLoading()
    {
        return $this->cacheLoading;
    }

    /**
     * @param mixed $cacheLoading
     */
    public function setCacheLoading($cacheLoading)
    {
        $this->cacheLoading = $cacheLoading;
    }

    //########################################

    public function isCacheEnabled()
    {
        return false;
    }

    public function getCacheLifetime()
    {
        return 60*60*24;
    }

    // ---------------------------------------

    public function getCacheGroupTags()
    {
        $modelName = str_replace('Ess\M2ePro\Model\\', '', $this->getHelper('Client')->getClassName($this));

        $tags[] = $modelName;

        if (strpos($modelName, '\\') !== false) {
            $allComponents = $this->getHelper('Component')->getComponents();
            $modelNameComponent = substr($modelName, 0, strpos($modelName, '\\'));

            if (in_array(strtolower($modelNameComponent), array_map('strtolower', $allComponents))) {
                $modelNameOnlyModel = substr($modelName, strpos($modelName, '\\')+1);
                $tags[] = $modelNameComponent;
                $tags[] = $modelNameOnlyModel;
            }
        }

        $tags = array_unique($tags);
        $tags = array_map('strtolower', $tags);

        return $tags;
    }

    public function getCacheInstancesTag()
    {
        if ($this->getId() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Method require loaded instance first');
        }

        return $this->getObjectModelName().'_'.$this->getId();
    }

    //########################################

    /**
     * @param $helperName
     * @param array $arguments
     * @return \Magento\Framework\App\Helper\AbstractHelper
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function getHelper($helperName, array $arguments = [])
    {
        return $this->helperFactory->getObject($helperName, $arguments);
    }

    //########################################
}
