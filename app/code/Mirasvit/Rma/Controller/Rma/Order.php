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


namespace Mirasvit\Rma\Controller\Rma;
use Magento\Framework\Controller\ResultFactory;

class Order extends \Mirasvit\Rma\Controller\Rma
{
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->orderFactory = $orderFactory;
        $this->registry     = $registry;

        parent::__construct($strategyFactory, $customerSession, $context);
    }

    /**
     * Shows list of RMAs in the order view page in customer account
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($orderId = $this->getRequest()->getParam('order_id')) {
            $order = $this->orderFactory->create()->load($orderId);
            $customer = $this->strategy->getPerformer();
            if ($order->getCustomerId() == $customer->getId()) {
                $this->registry->register('current_order', $order);
                if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
                    $navigationBlock->setActive('sales/order/history');
                }
                return $resultPage;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRequireCustomerAutorization()
    {
        return $this->strategy->isRequireCustomerAutorization();
    }
}