<?php


namespace Astralweb\RestrictedTimeProduct\Ui\DataProviders\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;

class EnableProductFrom extends AbstractModifier
{
    const SUGGEST_FILTER_URI = 'vendor_module/something/suggestCustomAttr';

    protected $urlBuilder;
    protected $locator;
    protected $arrayManager;

    /**
     * @param LocatorInterface            $locator
     * @param UrlInterface                $urlBuilder
     * @param ArrayManager                $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->customiseCustomAttrField($meta);

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Customise Custom Attribute field
     *
     * @param array $meta
     *
     * @return array
     */
    protected function customiseCustomAttrField(array $meta)
    {
        // $fieldCode:: attribute code
        $fieldCode = 'enable_product_from';
        $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
        $containerPath = $this->arrayManager->findPath(static::CONTAINER_PREFIX . $fieldCode, $meta, null, 'children');

        if (!$elementPath) {
            return $meta;
        }

        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Enable Product From'),
                            'dataScope'     => '',
                            'breakLine'     => false,
                            'formElement'   => 'container',
                            'componentType' => 'container',
                            'component'     => 'Magento_Ui/js/form/components/group',
                            'scopeLabel'    => __('[GLOBAL]'),
                        ],
                    ],
                ],
                'children'  => [
                    $fieldCode => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'component'     => 'Astralweb_RestrictedTimeProduct/js/form/element/date',
                                    'componentType' => Field::NAME,
                                    'disableLabel'  => true,
                                    'config'           => [
                                        'dataScope' => $fieldCode,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        );

        return $meta;
    }
}