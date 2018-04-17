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

class SaveMessage extends \Mirasvit\Rma\Controller\Rma
{
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Service\Rma\ShippingManagementInterface $shippingManagement,
        \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface $messageAddManagement,
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->registry             = $registry;
        $this->rmaRepository        = $rmaRepository;
        $this->rmaUrl               = $rmaUrl;
        $this->shippingManagement   = $shippingManagement;
        $this->messageAddManagement = $messageAddManagement;
        $this->resultFactory        = $context->getResultFactory();
        $this->customerSession      = $customerSession;

        parent::__construct($strategyFactory, $customerSession, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function isRequireCustomerAutorization()
    {
        return $this->strategy->isRequireCustomerAutorization();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $rma = $this->strategy->initRma($this->getRequest());
            if (!$this->registry->registry('current_rma')) {
                $this->registry->register('current_rma', $rma);
            }
            $isConfirmShipping = $this->getRequest()->getParam('shipping_confirmation');
            /// we need to confirm shipping BEFORE posting message
            /// (message can be from custom variables value in the shipping confirmation dialog)
            if ($isConfirmShipping) {
                $data = $this->getRequest()->getParams();
                $this->shippingManagement->confirmShipping($rma, $data);
                $this->messageManager->addSuccess(__('Shipping is confirmed. Thank you!'));
            }
            $message = $this->getRequest()->getParam('message');
            if (!($isConfirmShipping && !$message)) {
                $params = [
                    'isNotifyAdmin' => 1,
                    'isNotified'    => 0,
                ];
                $this->messageAddManagement->addMessage(
                    $this->strategy->getPerformer(),
                    $rma,
                    $message,
                    $params
                );
            }

            if (!$isConfirmShipping) {
                $this->messageManager->addSuccess(__('Your message was successfuly added'));
            }

            return $resultRedirect->setUrl($this->strategy->getRmaUrl($rma));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/index');
        }
    }
}
