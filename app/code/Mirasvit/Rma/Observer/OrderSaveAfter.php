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



namespace Mirasvit\Rma\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderSaveAfter implements ObserverInterface
{
    public function __construct(
        \Mirasvit\Rma\Model\OrderStatusHistoryFactory $orderStatusHistoryFactory,
        \Mirasvit\Rma\Repository\OrderStatusHistoryRepository $orderStatusHistoryRepository
    ) {
        $this->orderStatusHistoryFactory    = $orderStatusHistoryFactory;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        if (!$order = $observer->getEvent()->getOrder()) {
            return;
        }
        $status = $order->getStatus();
        $historyStatus = $this->orderStatusHistoryRepository->getByOrderId($order->getId());

        if ($status != $historyStatus->getStatus()) {
            $historyStatus->setOrderId($order->getId());
            $historyStatus->setStatus($status);
            $historyStatus->setCreatedAt(strtotime($order->getUpdatedAt()));
            $this->orderStatusHistoryRepository->save($historyStatus);
        }
    }
}
