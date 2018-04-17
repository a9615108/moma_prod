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

class MarkRead extends Rma
{
    public function __construct(
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->rmaFactory = $rmaFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = (int)$this->getRequest()->getParam('rma_id');
        $rma = $this->rmaFactory->create()->load($id);
        if (!$rma->getId()) {
            return $resultRedirect->setPath('*/*/');
        }
        try {
            $isRead = (int)$this->getRequest()->getParam('is_read');
            $rma->setIsAdminRead($isRead)->save();
            if ($isRead) {
                $message = __('Marked as read');
            } else {
                $message = __('Marked as unread');
            }
            $this->messageManager->addSuccess($message);
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/edit', ['id' => $rma->getId()]);
    }
}
