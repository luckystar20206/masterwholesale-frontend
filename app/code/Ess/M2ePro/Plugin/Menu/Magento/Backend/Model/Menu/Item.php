<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Plugin\Menu\Magento\Backend\Model\Menu;

use Ess\M2ePro\Helper\View;

/**
 * Class Item
 * @package Ess\M2ePro\Plugin\Menu\Magento\Backend\Model\Menu
 */
class Item extends \Ess\M2ePro\Plugin\AbstractPlugin
{
    private $menuTitlesUsing = [];

    protected $wizardHelper;
    protected $ebayView;
    protected $amazonView;
    protected $support;

    //########################################

    public function __construct(
        \Ess\M2ePro\Helper\Module\Wizard $wizardHelper,
        View\Ebay $ebayView,
        View\Amazon $amazonView,
        \Ess\M2ePro\Helper\Module\Support $support,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        $this->wizardHelper = $wizardHelper;
        $this->ebayView     = $ebayView;
        $this->amazonView   = $amazonView;
        $this->support      = $support;
        parent::__construct($helperFactory, $modelFactory);
    }

    //########################################

    /**
     * @param \Magento\Backend\Model\Menu\Item $interceptor
     * @param \Closure $callback
     * @return string
     */
    public function aroundGetClickCallback($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getClickCallback', $interceptor, $callback, $arguments);
    }

    // ---------------------------------------

    protected function processGetClickCallback($interceptor, \Closure $callback, array $arguments)
    {
        $id = $interceptor->getId();
        $urls = $this->getUrls();

        if (isset($urls[$id])) {
            return $this->renderOnClickCallback($urls[$id]);
        }

        return $callback(...$arguments);
    }

    //########################################

    /**
     * Gives able to display titles in menu slider which differ from titles in menu panel
     * @param \Magento\Backend\Model\Menu\Item $interceptor
     * @param \Closure $callback
     * @return string
     */
    public function aroundGetTitle($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getTitle', $interceptor, $callback, $arguments);
    }

    // ---------------------------------------

    protected function processGetTitle($interceptor, \Closure $callback, array $arguments)
    {
        if ($interceptor->getId() == View\Ebay::MENU_ROOT_NODE_NICK
            && !isset($this->menuTitlesUsing[View\Ebay::MENU_ROOT_NODE_NICK])
        ) {
            $ebayWizard = $this->wizardHelper->getActiveWizard(
                View\Ebay::NICK
            );

            if ($ebayWizard === null) {
                $this->menuTitlesUsing[View\Ebay::MENU_ROOT_NODE_NICK] = true;
                return 'eBay Integration';
            }
        }

        if ($interceptor->getId() == View\Amazon::MENU_ROOT_NODE_NICK
            && !isset($this->menuTitlesUsing[View\Amazon::MENU_ROOT_NODE_NICK])
        ) {
            $amazonWizard = $this->wizardHelper->getActiveWizard(
                View\Amazon::NICK
            );

            if ($amazonWizard === null) {
                $this->menuTitlesUsing[View\Amazon::MENU_ROOT_NODE_NICK] = true;
                return 'Amazon Integration';
            }
        }

        if ($interceptor->getId() == View\Walmart::MENU_ROOT_NODE_NICK
            && !isset($this->menuTitlesUsing[View\Walmart::MENU_ROOT_NODE_NICK])
        ) {
            $walmartWizard = $this->wizardHelper->getActiveWizard(
                View\Walmart::NICK
            );

            if ($walmartWizard === null) {
                $this->menuTitlesUsing[View\Walmart::MENU_ROOT_NODE_NICK] = true;
                return 'Walmart Integration';
            }
        }

        return $callback(...$arguments);
    }

    //########################################

    private function getUrls()
    {
        return [
            'Ess_M2ePro::ebay_help_center_documentation'   => $this->support->getDocumentationArticleUrl('x/2AIkAQ'),
            'Ess_M2ePro::ebay_help_center_ideas_workshop'  => $this->support->getIdeasComponentUrl('ebay'),
            'Ess_M2ePro::ebay_help_center_knowledge_base'  => $this->support->getKnowledgebaseComponentUrl('ebay'),
            'Ess_M2ePro::ebay_help_center_community_forum' => $this->support->getForumComponentUrl('ebay'),

            'Ess_M2ePro::amazon_help_center_documentation'   => $this->support->getDocumentationArticleUrl('x/3AIkAQ'),
            'Ess_M2ePro::amazon_help_center_ideas_workshop'  => $this->support->getIdeasComponentUrl('amazon'),
            'Ess_M2ePro::amazon_help_center_knowledge_base'  => $this->support->getKnowledgebaseComponentUrl('amazon'),
            'Ess_M2ePro::amazon_help_center_community_forum' => $this->support->getForumComponentUrl('amazon'),

            'Ess_M2ePro::walmart_help_center_documentation'   => $this->support->getDocumentationArticleUrl('x/JQBhAQ'),
            'Ess_M2ePro::walmart_help_center_ideas_workshop'  => $this->support->getIdeasComponentUrl('walmart'),
            'Ess_M2ePro::walmart_help_center_knowledge_base'  =>
                $this->support->getKnowledgebaseComponentUrl('walmart'),
            'Ess_M2ePro::walmart_help_center_community_forum' => $this->support->getForumComponentUrl('walmart'),
        ];
    }

    private function renderOnClickCallback($url)
    {
        return "window.open('$url', '_blank'); return false;";
    }

    //########################################
}
