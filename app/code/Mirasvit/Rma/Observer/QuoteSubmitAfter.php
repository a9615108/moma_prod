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

class QuoteSubmitAfter implements ObserverInterface
{
    public function __construct(
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory
    ) {
        $this->quoteSession  = $quoteSession;
        $this->objectManager = $objectManager;
        $this->rmaFactory    = $rmaFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        if ($rmaId = $this->quoteSession->getRmaId()) {
            /** @var \Mirasvit\Rma\Model\Rma\Interceptor $rma */
            $rma = $this->rmaFactory->create()->load($rmaId);
            $ids = $rma->getExchangeOrderIds();
            $ids[] = $order->getId();
            $rma->setExchangeOrderIds($ids);
            $rma->save();
            $this->quoteSession->unsetRmaId();
        }
    }
}
