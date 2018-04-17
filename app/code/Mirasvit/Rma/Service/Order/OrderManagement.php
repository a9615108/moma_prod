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


namespace Mirasvit\Rma\Service\Order;

use Magento\Sales\Api\Data\OrderInterface;

class OrderManagement implements \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface
{
    public function __construct(
        \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $policyConfig,
        \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory\CollectionFactory $historyCollectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->policyConfig             = $policyConfig;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->orderRepository          = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOrderList(\Magento\Customer\Model\Customer $customer)
    {
        $allowedStatuses = $this->policyConfig->getAllowRmaInOrderStatuses();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', $allowedStatuses, 'in')
            ->addFilter('customer_id', (int)$customer->getId())
            ->addFilter('entity_id', $this->OrderDateSql()->getColumnValues('order_id'), 'in')
            ;

        $items = $this->orderRepository->getList($searchCriteria->create())->getItems();
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function isReturnAllowed($order)
    {
        if (is_object($order)) {
            $order = $order->getId();
        }
        $allowedStatuses = $this->policyConfig->getAllowRmaInOrderStatuses();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', $allowedStatuses, 'in')
            ->addFilter('entity_id', (int)$order)
        ;

        return (bool)$this->orderRepository->getList($searchCriteria->create())->getTotalCount();
    }

    /**
     * @return \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory\Collection
     */
    public function OrderDateSql()
    {
        $allowedStatuses = $this->policyConfig->getAllowRmaInOrderStatuses();
        $returnPeriod    = (int)$this->policyConfig->getReturnPeriod();
        /** @var \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory\Collection $collection */
        $collection = $this->historyCollectionFactory->create();
        $collection->removeAllFieldsFromSelect()
            ->addFieldToSelect('order_id')
            ->addFieldToFilter('status', ['in' => $allowedStatuses])
            ->addFieldToFilter(
                new \Zend_Db_Expr('ADDDATE(created_at, '.$returnPeriod.')'),
                ['gt' => new \Zend_Db_Expr('NOW()')]
            )
        ;

        return $collection;
    }
}