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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class OrderStatusHistoryRepository implements \Mirasvit\Rma\Api\Repository\OrderStatusHistoryRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var \Mirasvit\Rma\Model\OrderStatusHistory[]
     */
    protected $instances = [];

    public function __construct(
        \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory\CollectionFactory $historyCollectionFactory,
        \Mirasvit\Rma\Model\OrderStatusHistoryFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory $historyResource
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->objectFactory            = $objectFactory;
        $this->historyResource          = $historyResource;
    }

    /**
     * {@inheritdoc}
     */
    public function save($history)
    {
        $this->historyResource->save($history);

        return $history;
    }

    /**
     * {@inheritdoc}
     */
    public function get($historyId)
    {
        if (!isset($this->instances[$historyId])) {
            /** @var \Mirasvit\Rma\Model\OrderStatusHistory $history */
            $history = $this->objectFactory->create();
            $history->load($historyId);
            if (!$history->getId() && !($history = $this->getByGuestId($historyId))) {
                throw NoSuchEntityException::singleField('id', $historyId);
            }
            $this->instances[$historyId] = $history;
        }
        return $this->instances[$historyId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByOrderStatus($orderId, $status)
    {
        /** @var \Mirasvit\Rma\Model\OrderStatusHistory $history */
        $history = $this->objectFactory->create()->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('status', $status)
                ->getFirstItem();
        $this->instances[$history->getHistoryId()] = $history;
    }

    /**
     * {@inheritdoc}
     */
    public function getByOrderId($orderId)
    {
        /** @var \Mirasvit\Rma\Model\OrderStatusHistory $history */
        $history = $this->objectFactory->create()->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->getFirstItem();
        $this->instances[$history->getHistoryId()] = $history;

        return $this->instances[$history->getHistoryId()];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\OrderStatusHistoryInterface $history)
    {
        try {
            $this->historyResource->delete($history);
            unset($this->instances[$history->getHistoryId()]);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete history with id %1',
                    $history->getId()
                ),
                $e
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        $history = $this->get($id);
        return  $this->delete($history);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        /** @var \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory\Collection $collection */
        return $this->historyCollectionFactory->create();
    }

}
