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


namespace Mirasvit\Rma\Service\Item;

class ItemListBuilder implements  \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface
{
    public function __construct(
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepositry,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $quantitySearchManagement,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
    ) {
        $this->itemRepositry            = $itemRepositry;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->quantitySearchManagement = $quantitySearchManagement;
        $this->itemFactory              = $itemFactory;
        $this->productRepository        = $productRepository;
        $this->orderItemFactory         = $orderItemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaItems($rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->create();

        return $this->itemRepositry->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $collection = $order->getItemsCollection();
        $items = [];
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($collection as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getProductType() == 'bundle') {
                $items[] = $this->buildFromOrderItem($item);
                foreach ($item->getChildrenItems() as $bundleItem) {
                    $rmaItem = $this->buildFromOrderItem($bundleItem);
                    $rmaItem->setIsBundleItem(true); //@todo fix here
                    $items[] = $rmaItem;
                }
            } else {
                $items[] = $this->buildFromOrderItem($item);
            }
        }

        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return \Mirasvit\Rma\Api\Data\ItemInterface
     */
    private function buildFromOrderItem($orderItem)
    {
        /** @var \Mirasvit\Rma\Api\Data\ItemInterface $rmaItem */
        $rmaItem = $this->itemFactory->create();
        $rmaItem->setOrderItemId($orderItem->getId());
        $rmaItem->setProductId($orderItem->getProductId());
        $rmaItem->setName($orderItem->getName());
        $rmaItem->setProductOptions($orderItem->getProductOptions());
        $qtyShipped = $orderItem->getQtyShipped();

        $status = '0';
        if ($product = $orderItem->getProduct()) {
            $status = $product->getRmaStatus();
        } elseif ($orderItem->getSku()) {
            $product = $this->productRepository->get($orderItem->getSku());
            $status  = $product->getRmaStatus();
            $rmaItem->setProductId($product->getId());
        }

        $rmaItem->setIsRmaAllowed((string) $status !== '0');

        // we have option to allow rma when status is processing (for example). so products are not shipped yet.
        if ($qtyShipped == 0) {
            $qtyShipped = $orderItem->getQtyOrdered();
        }
        $qty = $qtyShipped - $this->quantitySearchManagement->getQtyInRma($rmaItem);
        if ($qty < 0) {
            $qty = 0;
        }
        $rmaItem->setQtyAvailable($qty);

        return $rmaItem;
    }
}