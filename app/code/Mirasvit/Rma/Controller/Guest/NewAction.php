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



namespace Mirasvit\Rma\Controller\Guest;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

class NewAction extends Action
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Order\LoginInterface $orderLoginService,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->orderLoginService = $orderLoginService;
        $this->customerSession = $customerSession;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //we need this for demo purposes
        if ($this->getRequest()->getParam('c') !== null) {
            $this->customerSession->logout();
        }
        if ($this->customerSession->isLoggedIn()) {
            return $resultRedirect->setPath('returns/rma/new');
        }
        try {
            $order = $this->orderLoginService->getOrder(
                $this->getRequest()->getParam('order_increment_id'),
                $this->getRequest()->getParam('email')
            );
            if ($order) {
                $this->customerSession->setRMAGuestOrderId($order->getId());
                return $resultRedirect->setPath('returns/rma/list');
            } elseif ($this->getRequest()->getParam('order_increment_id')) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Wrong Order #, Email or Last Name'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
