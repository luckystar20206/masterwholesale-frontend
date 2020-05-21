<?php
namespace Mwi\StoreTiming\Setup;
 
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    const YOUR_STORE_ID = 1;
 
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $_blockFactory;
 
    /**
     * UpgradeData constructor
     *
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        \Magento\Cms\Model\BlockFactory $blockFactory
    )
    {
        $this->_blockFactory = $blockFactory;
    }
 
    /**
     * Upgrade data for the module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        // run the code while upgrading module to version 0.1.1
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $cmsBlock = $this->_blockFactory->create()->setStoreId(self::YOUR_STORE_ID)->load('porto_custom_block_for_header_home5', 'identifier');
            
            $cmsBlockData = [
                'title' => 'Porto - Custom Block for Header Home 5',
                'identifier' => 'porto_custom_block_for_header_home5',
                'is_active' => 1,
                'stores' => [self::YOUR_STORE_ID],
                'content' => '<div id="phone-hrs">
    <a href="https://goo.gl/maps/seJQCD3gzao" target="_blank">Visit Us</a> or Call Us: <a id="phone-number" href="tel:+18009387925">(800) 938-7925</a> &nbsp;&nbsp; {{block class="Magento\Framework\View\Element\Template" template="Mwi_StoreTiming::timing.phtml" name="store_timing"}}
</div>',
            ];
 
            if (!$cmsBlock->getId()) {
                $this->_blockFactory->create()->setData($cmsBlockData)->save();
            } else {
                $cmsBlock->setContent($cmsBlockData['content'])->save();
            }
        }
 
        $setup->endSetup();
    }
}