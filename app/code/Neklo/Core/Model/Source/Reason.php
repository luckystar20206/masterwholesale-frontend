<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\Core\Model\Source;

class Reason implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Neklo\Core\Helper\Extension
     */
    public $extensionHelper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    public $metadata;

    public function __construct(
        \Neklo\Core\Helper\Extension $extensionHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->extensionHelper = $extensionHelper;
        $this->metadata = $productMetadata;
    }

    public function toOptionArray()
    {
        $reasonList = [];
        $reasonList[] = [
            'value' => '',
            'label' => __('Please select')
        ];

        $reasonList[] = [
            'value' => 'Magento v' . $this->metadata->getVersion(),
            'label' => __('Magento Related Support')
        ];
        $reasonList[] = [
            'value' => 'New Extension',
            'label' => __('Request New Extension Development')
        ];

        $moduleList = $this->extensionHelper->getModuleList();
        foreach ($moduleList as $moduleCode => $moduleData) {
            $moduleTitle = $moduleData['name'] . ' v' . $moduleData['version'];
            $reasonList[] = [
                'value' => $moduleCode . ' ' . $moduleData['version'],
                'label' => __(sprintf('%s Support', $moduleTitle))
            ];
        }

        $reasonList[] = ['value' => 'other', 'label' => __('Other Reason')];

        return $reasonList;
    }
}
