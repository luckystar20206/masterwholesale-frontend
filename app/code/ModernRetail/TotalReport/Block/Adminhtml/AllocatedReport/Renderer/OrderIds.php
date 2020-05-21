<?php
namespace  ModernRetail\TotalReport\Block\Adminhtml\AllocatedReport\Renderer;
class OrderIds
    extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Helper\Data $mageHelper,
        array $data = array()
    ) {
        $this->helper = $mageHelper;
        parent::__construct($context, $data);
    }


    public function render(\Magento\Framework\DataObject $row)
    {
		
        $allocated_orders = explode(",",$row->getData('order_ids'));
		
        $ids = explode(",",$row->getOrderIds());
        $ids = array_unique($ids);
        $ids = array_filter($ids, 'strlen');
		
        //var_dump($allocated_orders);
        $html = array(); 
        $key = -1;
        foreach($ids as $id){
            $key++;
            list($id,$increment) = explode("_",$id);
            if (intval($allocated_orders[$key])==0) continue;
            $html[] = "<a  target='__blank' href='".$this->helper->getUrl("sales/order/view",array("order_id"=>$id))."'>".$increment."</a> &nbsp;";

        }

        return join("",$html);

    }

}