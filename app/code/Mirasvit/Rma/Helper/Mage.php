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



namespace Mirasvit\Rma\Helper;

class Mage extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Url $backendUrlManager
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->context                = $context;
        $this->backendUrlManager      = $backendUrlManager;

        parent::__construct($context);
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getBackendOrderUrl($orderId)
    {
        return $this->backendUrlManager->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrderCollection()
    {
        $collection = $this->orderCollectionFactory->create()
            ->setOrder('entity_id');

        return $collection;
    }
}
