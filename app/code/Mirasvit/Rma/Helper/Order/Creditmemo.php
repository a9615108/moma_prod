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



namespace Mirasvit\Rma\Helper\Order;

use Mirasvit\Rma\Model\Resolution;

/**
 * Helper for CreditMome
 */
class Creditmemo extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Sales\Model\Order\Creditmemo $creditmemoModel,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection,
        \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface $resolutionRepository,
        \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface $itemListBuilder,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->backendUrlManager    = $backendUrlManager;
        $this->moduleManager        = $context->getModuleManager();
        $this->creditmemoModel      = $creditmemoModel;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->invoiceCollection    = $invoiceCollection;
        $this->resolutionRepository = $resolutionRepository;
        $this->itemListBuilder      = $itemListBuilder;

        parent::__construct($context);
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma    $rma
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool
     */
    public function canCreateCreditmemo($rma, $order)
    {
        $allowCreateCreditmemo = false;

        if (!$order->canCreditmemo()) {
            return $allowCreateCreditmemo;
        }

        $creditModuleInstalled = $this->moduleManager->isEnabled('Mirasvit_Credit');
        if ($rma->getCreditMemoIds()) {
            foreach ($rma->getCreditMemoIds() as $id) {
                $creditmemo = $this->creditmemoRepository->get($id);
                if ($creditmemo->getOrderId() == $order->getId()) {
                    return $allowCreateCreditmemo;
                }
            }
        }

        $allowedStatuses = [
            $this->resolutionRepository->getByCode(Resolution::REFUND)->getId(),
            $this->resolutionRepository->getByCode(Resolution::CREDIT)->getId()
        ];

        if ($creditModuleInstalled) {
            $this->creditmemoModel->setOrder($order);
            $realPaidAmount = $this->creditmemoModel->roundPrice($order->getTotalPaid() + $order->getCreditInvoiced());
            $realRefunded   = $this->creditmemoModel->roundPrice(
                $order->getTotalRefunded() + $order->getCreditTotalRefunded()
            );
            if (abs($realPaidAmount - $realRefunded) < .0001) {
                return $allowCreateCreditmemo;
            }

            foreach ($this->itemListBuilder->getRmaItems($rma) as $item) {
                if (in_array($item->getResolutionId(), $allowedStatuses)) {
                    $allowCreateCreditmemo = true;
                    break;
                }
            }
        } else {
            $allowCreateCreditmemo = true;
        }

        return $allowCreateCreditmemo;
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma    $rma
     * @param \Magento\Sales\Model\Order $order
     *
     * @return string
     */
    public function getCreditmemoUrl($rma, $order)
    {
        $collection = $this->invoiceCollection->addFieldToFilter('order_id', $order->getId());

        if ($collection->count() == 1) {
            $invoice = $collection->getFirstItem();

            return $this->backendUrlManager->getUrl(
                'sales/order_creditmemo/start',
                ['order_id' => $order->getId(), 'invoice_id' => $invoice->getId(), 'rma_id' => $rma->getId()]
            );
        } else {
            return $this->backendUrlManager->getUrl(
                'sales/order_creditmemo/start',
                ['order_id' => $order->getId(), 'rma_id' => $rma->getId()]
            );
        }
    }

}