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



namespace Mirasvit\Rma\Block\Rma\View;

class Items extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Service\Config\RmaRequirementConfig $config,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->rmaSearchManagement   = $rmaSearchManagement;
        $this->config                = $config;
        $this->registry              = $registry;
        $this->imageHelper           = $imageHelper;
        $this->rmaItemHtml           = $rmaItemHtml;
        $this->itemManagement        = $itemManagement;
        $this->itemProductManagement = $itemProductManagement;
        $this->context               = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isReasonAllowed()
    {
        return $this->config->isCustomerReasonRequired();
    }

    /**
     * @return bool
     */
    public function isConditionAllowed()
    {
        return $this->config->isCustomerConditionRequired();
    }

    /**
     * @return bool
     */
    public function isResolutionAllowed()
    {
        return $this->config->isCustomerResolutionRequired();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getItems()
    {
        return $this->rmaSearchManagement->getRequestedItems($this->getRma());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @param string $imageId
     * @return \Magento\Catalog\Helper\Image
     */
    public function initImage($item, $imageId)
    {
        $product = $this->getProduct($item);

        return $this->imageHelper->init($product, $imageId);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemProductManagement->getProduct($item);
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
     * @return string
     */
    public function getOrderItemSku(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemSku($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemWeight(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $weight = $this->getProduct($item)->getWeight() * $item->getQtyRequested();

        if (!$weight) {
            $weight = '--';
        }

        return $weight;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getReasonName(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemManagement->getReasonName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getConditionName(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemManagement->getConditionName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getResolutionName(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemManagement->getResolutionName($item);
    }
}
