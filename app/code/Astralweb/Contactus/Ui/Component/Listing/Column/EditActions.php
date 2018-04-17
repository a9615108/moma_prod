<?php

namespace Astralweb\Contactus\Ui\Component\Listing\Column;

class EditActions extends \Magento\Ui\Component\Listing\Columns\Column {

    const URL_PATH_EDIT = 'contactus/contact/edit';
    const URL_PATH_DELETE = 'contactus/contact/delete';
    protected $urlBuilder;
    private $editUrl;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::URL_PATH_EDIT
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $title = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$title]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['id']]),
                        'label' => __('Edit')
                    ];
                    $item[$title]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['id' => $item['id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete "${ $.$data.title }"'),
                            'message' => __('Are you sure you want to delete a "${ $.$data.title }" record?')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}