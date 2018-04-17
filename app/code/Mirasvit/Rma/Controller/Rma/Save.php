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
use Magento\Framework\App\Action\Context;

class Save extends \Mirasvit\Rma\Controller\Rma
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface $rmaSaveService,
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        PostDataProcessor $dataProcessor,
        \Magento\Customer\Model\Session $customerSession,
        Context $context
    ) {
        $this->customerSession = $customerSession;
        $this->dataProcessor   = $dataProcessor;
        $this->rmaSaveService  = $rmaSaveService;
        $this->rmaUrl          = $rmaUrl;

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

        $data = $this->getRequest()->getParams();

        try {
            $rma = $this->rmaSaveService->saveRma(
                $this->strategy->getPerformer(),
                $this->dataProcessor->filterRmaData($data),
                $this->dataProcessor->filterRmaItems($data)
            );

            $this->messageManager->addSuccess(__('RMA was successfuly created'));

            return $resultRedirect->setUrl($this->strategy->getRmaUrl($rma));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->customerSession->setFormData($data);
            if ($this->getRequest()->getParam('id')) {
                return $resultRedirect->setPath('*/*/view', ['id' => $this->getRequest()->getParam('id')]);
            } else {
                return $resultRedirect->setPath('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
            }
        }
    }
}
