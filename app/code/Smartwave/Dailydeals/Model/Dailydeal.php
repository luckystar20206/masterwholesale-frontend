<?php
namespace Smartwave\Dailydeals\Model;

class Dailydeal extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'sw_dailydeals_dailydeal';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'sw_dailydeals_dailydeal';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sw_dailydeals_dailydeal';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smartwave\Dailydeals\Model\ResourceModel\Dailydeal');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
