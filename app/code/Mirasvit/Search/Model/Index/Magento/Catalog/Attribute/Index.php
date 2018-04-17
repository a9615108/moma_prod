<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-sphinx
 * @version   1.0.61
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Model\Index\Magento\Catalog\Attribute;

use Magento\Eav\Model\Config as EavConfig;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\IndexerFactory;
use Mirasvit\Search\Model\Index\SearcherFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;

class Index extends AbstractIndex
{
    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param EavConfig                  $eavConfig
     * @param ObjectManagerInterface     $objectManager
     * @param IndexerFactory             $indexer
     * @param SearcherFactory            $searcher
     */
    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory,
        EavConfig $eavConfig,
        ObjectManagerInterface $objectManager,
        IndexerFactory $indexer,
        SearcherFactory $searcher
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->objectManager = $objectManager;

        parent::__construct($indexer, $searcher);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Attribute')->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return __('Magento')->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'magento_catalog_attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'label' => __('Attribute value (option)'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldsets()
    {
        return ['\Mirasvit\Search\Block\Adminhtml\Index\Edit\Properties\Magento\Catalog\Attribute'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'value';
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute[]
     */
    public function getCatalogAttributes()
    {
        return $this->attributeCollectionFactory->create()
            ->addVisibleFilter()
            ->addDisplayInAdvancedSearchFilter()
            ->setOrder('attribute_id', 'asc');
    }

    /**
     * {@inheritdoc}
     */
    protected function buildSearchCollection()
    {
        $ids = $this->searcher->getMatchedIds();

        $collection = new Collection(new EntityFactory($this->objectManager));

        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->getModel()
            ->getProperty('attribute'));
        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                if (in_array($option['value'], $ids)) {
                    $collection->addItem(
                        new DataObject($option)
                    );
                }
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collection = new Collection(new EntityFactory($this->objectManager));

        if ($lastEntityId) {
            return $collection;
        }

        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->getModel()->getProperty('attribute'));
        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                $collection->addItem(
                    new DataObject($option)
                );
            }
        }

        return $collection;
    }

}
