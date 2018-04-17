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



namespace Mirasvit\Rma\Block\Rma\NewRma\Step2\Items;

class Item extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rma\Api\Data\ItemInterface
     */
    protected $item;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQuantityManagement,
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->itemQuantityManagement = $itemQuantityManagement;
        $this->strategy               = $strategyFactory->create();
        $this->rmaFactory             = $rmaFactory;
        $this->itemManagement         = $itemManagement;
        $this->itemProductManagement  = $itemProductManagement;
        $this->rmaSearchManagement    = $rmaSearchManagement;
        $this->imageHelper            = $imageHelper;
        $this->rmaItemHtml            = $rmaItemHtml;
        $this->context                = $context;

        parent::__construct($context, $data);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return $this
     */
    public function setItem(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface  $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProductByItem($item)
    {
        return $this->itemProductManagement->getProduct($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getRmasByItem($item)
    {
        $orderItem = $this->itemManagement->getOrderItem($item);
        $result = [];
        foreach ($this->getRmaItemsByOrderItem($orderItem) as $item) {
            $rma = $this->rmaFactory->create()->load($item->getRmaId());
            $result[] = "<a href='{$this->strategy->getRmaUrl($rma)}' target='_blank'>#{$rma->getIncrementId()}</a>";
        }

        return implode(', ', $result);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getRmaItemsByOrderItem($orderItem)
    {
        return $this->rmaSearchManagement->getRmaItemsByOrderItem($orderItem->getItemId());
    }

    /**
     * Initialize Helper to work with Image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Helper\Image
     */
    public function initImage($product, $imageId, $attributes = [])
    {
        return $this->imageHelper->init($product, $imageId, $attributes);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getOrderItemLabel(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemLabel($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getQtyAvailable(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyAvailable($item);
    }
}