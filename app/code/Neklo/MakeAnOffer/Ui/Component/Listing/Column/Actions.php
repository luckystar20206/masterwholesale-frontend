<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Ui\Component\Listing\Column;

use Neklo\MakeAnOffer\Model\Source\Status;

class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const URL_PATH_RESPOND = 'neklo_makeanoffer/request/respond';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * constructor
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['id'])) {
                    if ($item['status'] == Status::NEW_REQUEST_STATUS
                        || $item['status'] == Status::PENDING_REQUEST_STATUS
                    ) {
                        $label = __('Respond');
                    } else {
                        $label = __('View');
                    }

                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_RESPOND,
                                [
                                    'request_id' => $item['id']
                                ]
                            ),
                            'label' => $label
                        ],
                    ];
                }
            }
        }
        return $dataSource;
    }
}
