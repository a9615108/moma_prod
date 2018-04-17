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



namespace Mirasvit\Rma\Controller\Adminhtml\Rma;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rma\Controller\Adminhtml\Rma;
use \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface;

class Save extends Rma
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface $performer,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface $rmaSaveService,
        \Mirasvit\Rma\Controller\Adminhtml\Rma\PostDataProcessor $dataProcessor,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->performer      = $performer;
        $this->rmaSaveService = $rmaSaveService;
        $this->dataProcessor  = $dataProcessor;

        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data = $this->getRequest()->getParams()) {
            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit',
                    ['id' => $data['rma_id'], '_current' => true]);
            }
            try {
                $performer = $this->performer->create(PerformerFactoryInterface::USER, $this->_auth->getUser());
                $rma = $this->rmaSaveService->saveRma(
                    $performer,
                    $this->dataProcessor->filterRmaData($data),
                    $this->dataProcessor->filterRmaItems($data)
                );

                $this->messageManager->addSuccess(__('RMA was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $rma->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->backendSession->setFormData($data);
                if ($this->getRequest()->getParam('id')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                } else {
                    return $resultRedirect->setPath(
                        '*/*/add',
                        ['order_id' => $this->getRequest()->getParam('order_id')]
                    );
                }
            }
        }
        $this->messageManager->addError(__('Unable to find rma to save'));

        return $resultRedirect->setPath('*/*/');
    }
}
