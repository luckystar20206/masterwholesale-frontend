<?php
namespace ModernRetail\Import\Block\System\Config\Fieldset;

abstract class MappingAbstract extends  \Magento\Config\Block\System\Config\Form\Fieldset {


    public $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = [])
    {
        $this->scopeConfig = $context->getScopeConfig();;
        parent::__construct($context,$authSession,$jsHelper,$data);

    }



    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);

        $html = $this->_getHeaderHtml($element);



        foreach ($element->getElements() as $field) {
            if ($field instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
                $html .= '<tr id="row_' . $field->getHtmlId() . '"><td colspan="4">' . $field->toHtml() . '</td></tr>';
            } else {
                $html .= $field->toHtml();
            } 
        }
        $html .= '</tbody></table>';

        $html.= $this->getLayout()->createBlock("ModernRetail\Import\Block\System\Config\Fieldset\Mapping\Render")->setMappingType($this->_mappingType)->toHtml();

        $html .= $this->_getFooterHtml($element);

        return $html;
    }



    protected function _getFooterHtml($element)
    {
       // $html = '</tbody></table>';
        $html = "";
        foreach ($element->getElements() as $field) {
            if ($field->getTooltip()) {
                $html .= sprintf(
                    '<div id="row_%s_comment" class="system-tooltip-box" style="display:none;">%s</div>',
                    $field->getId(),
                    $field->getTooltip()
                );
            }
        }
        $html .= '</fieldset>' . $this->_getExtraJs($element);

        if ($element->getIsNested()) {
            $html .= '</td></tr>';
        } else {
            $html .= '</div>';
        }
        return $html;
    }




}