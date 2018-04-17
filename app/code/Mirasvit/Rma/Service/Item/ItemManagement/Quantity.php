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


namespace Mirasvit\Rma\Service\Item\ItemManagement;

/**
 *  We put here only methods directly connected with Item properties
 */
class Quantity implements \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface
{
    public function __construct(
        \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $rmaPolicyConfig,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
    ) {
        $this->rmaPolicyConfig        = $rmaPolicyConfig;
        $this->itemManagement         = $itemManagement;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->stockState             = $stockState;
        $this->rmaRepository          = $rmaRepository;
        $this->itemRepository         = $itemRepository;
        $this->rmaSearchManagement    = $rmaSearchManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyStock($productId)
    {
        return $this->stockState->getStockQty($productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyOrdered(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return (int) $this->itemManagement->getOrderItem($item)->getQtyOrdered();
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyAvailable(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        if ($this->rmaPolicyConfig->isAllowRmaRequestOnlyShipped()) {
            $orderItem = $this->itemManagement->getOrderItem($item);
            $qty = $orderItem->getQtyShipped() > $item->getQtyAvailable() ?
                $item->getQtyAvailable() :
                $orderItem->getQtyShipped();
        } else {
            $qty = $this->getQtyOrdered($item) - $this->getItemQtyReturned($item);
        }

        return (int)$qty;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemQtyReturned(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $orderItem      = $this->itemManagement->getOrderItem($item);
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('order_id', $orderItem->getOrderId());

        $qtyReturned = 0;
        foreach ($this->rmaRepository->getList($searchCriteria->create())->getItems() as $rma) {
            foreach ($this->rmaSearchManagement->getItems($rma) as $rmaItem) {
                if ($rmaItem->getProductId() == $item->getProductId()) {
                    $qtyReturned = $qtyReturned + $rmaItem->getQtyRequested();
                }
            }
        }

        return $qtyReturned;
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyInRma($orderItem)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_item_id', $this->itemManagement->getOrderItem($orderItem)->getId())
        ;

        $items = $this->itemRepository->getList($searchCriteria->create())->getItems();
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->getQtyRequested();
        }

        return $sum;
    }
}