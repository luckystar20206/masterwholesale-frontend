<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Walmart\Marketplace\Edit;

use Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm;

/**
 * Class Form
 * @package Ess\M2ePro\Block\Adminhtml\Walmart\Marketplace\Edit
 */
class Form extends AbstractForm
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->css->addFile('marketplace/form.css');
    }

    //########################################

    protected function _prepareForm()
    {
        $this->prepareData();
        $componentName = $this->getHelper('Component\Walmart')->getTitle();

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        foreach ($this->groups as $group) {
            $fieldset = $form->addFieldset(
                'marketplaces_group_'.$group['id'],
                ['legend' => $this->__($group['title'])]
            );

            foreach ($group['marketplaces'] as $marketplace) {
                $afterElementHtml = '
                <div id="run_single_button_'.$marketplace['instance']->getId().'" class="control-value"';
                $marketplace['instance']->getStatus() == \Ess\M2ePro\Model\Marketplace::STATUS_DISABLE &&
                $afterElementHtml .= ' style="display: none;"';
                $afterElementHtml .= '">';

                $afterElementHtml .= $this->getLayout()
                    ->createBlock(\Magento\Backend\Block\Widget\Button::class)
                    ->setData([
                        'label'   => $this->__('Update Now'),
                        'onclick' => 'MarketplaceObj.runSingleSynchronization(this)',
                        'class' => 'run_single_button primary'
                    ])->toHtml();

                $afterElementHtml .= <<<HTML
                </div>
                <div id="synch_info_container" class="control-value">
                    <div id="synch_info_wait_{$marketplace['instance']->getId()}"
                        class="value" style="display: none; color: gray;">&nbsp; {$this->__('Waiting')}</div>

                    <div id="synch_info_process_{$marketplace['instance']->getId()}"
                        class="value" style="display: none; color: blue;">&nbsp; {$this->__('Processing')}</div>

                    <div id="synch_info_complete_{$marketplace['instance']->getId()}"
                        class="value" style="display: none; color: green;">{$this->__('Completed')}</div>

                    <div id="marketplace_title_{$marketplace['instance']->getId()}"
                        class="value" style="display: none;">{$marketplace['instance']->getTitle()}</div>
                </div>
                <div id="changed_{$marketplace['instance']->getId()}" class="changed control-value"
                    style="display: none;">
                </div>
HTML;

                $selectData = [
                    'label' => $this->__($marketplace['instance']->getData('title')),
                    'style' => 'display: inline-block;',
                    'after_element_html' => $afterElementHtml
                ];

                if ($marketplace['params']['locked']) {
                    $selectData['disabled'] = 'disabled';
                    $selectData['values'] = [
                        \Ess\M2ePro\Model\Marketplace::STATUS_ENABLE => $this->__('Enabled') . ' - ' .
                            $this->__('Used in Account(s)')
                    ];
                    $selectData['value'] = \Ess\M2ePro\Model\Marketplace::STATUS_ENABLE;
                } else {
                    $selectData['values'] = [
                        \Ess\M2ePro\Model\Marketplace::STATUS_DISABLE => $this->__('Disabled'),
                        \Ess\M2ePro\Model\Marketplace::STATUS_ENABLE => $this->__('Enabled')
                    ];
                    $selectData['value'] = $marketplace['instance']->getStatus();
                }

                $selectData['name'] = 'status_'.$marketplace['instance']->getId();
                $selectData['class'] = 'marketplace_status_select';
                $selectData['note'] = $marketplace['instance']->getUrl();

                $fieldset->addField(
                    'status_'.$marketplace['instance']->getId(),
                    self::SELECT,
                    $selectData
                )->addCustomAttribute('marketplace_id', $marketplace['instance']->getId())
                 ->addCustomAttribute('markeptlace_component_name', $componentName)
                 ->addCustomAttribute('onchange', 'MarketplaceObj.changeStatus(this);');
            }
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    //########################################

    protected function prepareData()
    {
        // ---------------------------------------
        /** @var \Ess\M2ePro\Model\Marketplace $tempMarketplaces */
        $tempMarketplaces = $this->parentFactory->getObject(\Ess\M2ePro\Helper\Component\Walmart::NICK, 'Marketplace')
            ->getCollection()
            ->setOrder('group_title', 'ASC')
            ->setOrder('sorder', 'ASC')
            ->setOrder('title', 'ASC')
            ->getItems();

        $storedStatuses = [];
        $groups = [];
        $idGroup = 1;

        $groupOrder = [
            'america' => 'America'
        ];

        foreach ($groupOrder as $key => $groupOrderTitle) {
            $groups[$key] = [
                'id'           => $idGroup++,
                'title'        => $groupOrderTitle,
                'marketplaces' => []
            ];

            foreach ($tempMarketplaces as $tempMarketplace) {
                if ($groupOrderTitle != $tempMarketplace->getGroupTitle()) {
                    continue;
                }

                $isLocked = (bool)$this->parentFactory
                    ->getObject(\Ess\M2ePro\Helper\Component\Walmart::NICK, 'Account')->getCollection()
                    ->addFieldToFilter('marketplace_id', $tempMarketplace->getId())
                    ->getSize();

                $storedStatuses[] = [
                    'marketplace_id' => $tempMarketplace->getId(),
                    'status'         => $tempMarketplace->getStatus()
                ];

                /** @var $tempMarketplace \Ess\M2ePro\Model\Marketplace */
                $marketplace = [
                    'instance' => $tempMarketplace,
                    'params'   => ['locked' => $isLocked]
                ];

                $groups[$key]['marketplaces'][] = $marketplace;
            }
        }

        $this->groups = $groups;
        $this->storedStatuses = $storedStatuses;
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        $this->jsUrl->addUrls([
            'formSubmit' => $this->getUrl('m2epro/walmart_marketplace/save'),
            'logViewUrl' => $this->getUrl(
                '*/walmart_synchronization_log/index',
                ['back'=>$this->getHelper('Data')
                ->makeBackUrlParam('*/walmart_synchronization/index')]
            ),

            'runSynchNow' => $this->getUrl('*/walmart_marketplace/runSynchNow'),
            'synchCheckProcessingNow' => $this->getUrl('*/walmart_synchronization/synchCheckProcessingNow')
        ]);

        $this->jsTranslator->addTranslations([
            'Settings have been saved.' => $this->__('Settings have been saved.'),
            'You must select at least one Site you will work with.' =>
                $this->__('You must select at least one Site you will work with.'),

            'Another Synchronization Is Already Running.' => $this->__('Another Synchronization Is Already Running.'),
            'Getting information. Please wait ...' => $this->__('Getting information. Please wait ...'),
            'Preparing to start. Please wait ...' => $this->__('Preparing to start. Please wait ...'),

            'Synchronization has successfully ended.' => $this->__('Synchronization has successfully ended.'),
            'Synchronization ended with warnings. <a target="_blank" href="%url%">View Log</a> for details.' =>
                $this->__(
                    'Synchronization ended with warnings. <a target="_blank" href="%url%">View Log</a> for details.'
                ),
            'Synchronization ended with errors. <a target="_blank" href="%url%">View Log</a> for details.' =>
                $this->__(
                    'Synchronization ended with errors. <a target="_blank" href="%url%">View Log</a> for details.'
                )
        ]);

        $storedStatuses = $this->getHelper('Data')->jsonEncode($this->storedStatuses);
        $this->js->addOnReadyJs(<<<JS
            require([
                'M2ePro/Marketplace',
                'M2ePro/SynchProgress',
                'M2ePro/Plugin/ProgressBar',
                'M2ePro/Plugin/AreaWrapper'
            ], function() {
                window.MarketplaceProgressBarObj = new ProgressBar('marketplaces_progress_bar');
                window.MarketplaceWrapperObj = new AreaWrapper('marketplaces_content_container');

                window.MarketplaceProgressObj = new SynchProgress(MarketplaceProgressBarObj, MarketplaceWrapperObj );
                window.MarketplaceObj = new Marketplace(MarketplaceProgressObj, $storedStatuses);
                window.MarketplaceProgressObj.initPageCheckState();
            });
JS
        );

        return parent::_toHtml();
    }

    //########################################
}
