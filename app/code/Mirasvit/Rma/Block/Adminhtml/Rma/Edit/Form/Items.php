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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;


class Items extends \Magento\Backend\Block\Template
{

    public function __construct(
        \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface $itemListBuilder,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQuantityManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Helper\Item\Option $rmaItemOption,
        \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->itemListBuilder        = $itemListBuilder;
        $this->itemQuantityManagement = $itemQuantityManagement;
        $this->itemProductManagement  = $itemProductManagement;
        $this->rmaManagement          = $rmaManagement;
        $this->rmaItemOption          = $rmaItemOption;
        $this->rmaItemHtml            = $rmaItemHtml;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->getData('rma');
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->rmaManagement->getOrder($this->getRma());
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getRmaItems()
    {
        $rma = $this->getRma();
        if ($rma->getId()) {
            return $this->itemListBuilder->getRmaItems($rma);
        } else {
            return $this->itemListBuilder->getList($this->getOrder());
        }
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
     * @return \Mirasvit\Rma\Api\Data\ReturnInterface[]
     */
    public function getConditionList()
    {
        return $this->rmaItemOption->getConditionList();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ReturnInterface[]
     */
    public function getResolutionList()
    {
        return $this->rmaItemOption->getResolutionList();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ReturnInterface[]
     */
    public function getReasonList()
    {
        return $this->rmaItemOption->getReasonList();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return boolean|int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIsBundleItem(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return false;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getQtyStock(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyStock($item->getProductId());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getQtyOrdered(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyOrdered($item);
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