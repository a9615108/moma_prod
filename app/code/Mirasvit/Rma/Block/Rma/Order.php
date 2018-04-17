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



namespace Mirasvit\Rma\Block\Rma;

class Order extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface $orderManagement,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->registry        = $registry;
        $this->orderManagement = $orderManagement;
        $this->context         = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getCurrentOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return bool
     */
    public function isOrderPage()
    {
        return is_object($this->getCurrentOrder());
    }

    /**
     * @return bool
     */
    public function isReturnAllowed()
    {
        if ($order = $this->getCurrentOrder()) {
            return $this->orderManagement->isReturnAllowed($order);
        }
    }

    /**
     * @return string
     */
    public function getOrderRmaList()
    {
        /** @var \Mirasvit\Rma\Block\Rma\Listing\Listing $listBlock */
        $listBlock = $this->getChildBlock('rma.list.list');
        $listBlock->setCurrentOrder($this->getCurrentOrder());

        return $listBlock->toHtml();
    }
}
