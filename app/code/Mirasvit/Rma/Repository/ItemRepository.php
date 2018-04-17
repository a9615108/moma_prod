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
 * @package   mirasvit/module-rma
 * @version   1.1.22
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Repository;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Select/insert/update of RMA items in DB
 */
class ItemRepository implements \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Item[]
     */
    protected $instances = [];

    public function __construct(
        \Mirasvit\Rma\Model\ItemFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Item $itemResource,
        \Mirasvit\Rma\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory         = $objectFactory;
        $this->itemResource          = $itemResource;
        $this->searchResultsFactory  = $searchResultsFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $this->itemResource->save($item);
        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function get($itemId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$itemId][$cacheKey])) {
            /** @var Item $item */
            $item = $this->objectFactory->create();
            if (null !== $storeId) {
                $item->setStoreId($storeId);
            }
            $item->load($itemId);
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField('id', $itemId);
            }
            $this->instances[$itemId][$cacheKey] = $item;
        }
        return $this->instances[$itemId][$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        try {
            $itemId = $item->getId();
            $this->itemResource->delete($item);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete item with id %1',
                    $item->getId()
                ),
                $e
            );
        }
        unset($this->instances[$itemId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($itemId)
    {
        $item = $this->get($itemId);
        return  $this->delete($item);
    }

    /**
     * Validate item process
     *
     * @param  Item $item
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateItem(Item $item)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        /** @var \Mirasvit\Rma\Model\ResourceModel\Rma\Collection $collection */
        return $this->itemCollectionFactory->create();
    }
}
