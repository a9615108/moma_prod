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
use Mirasvit\Rma\Controller\Adminhtml\Rma;

class Add extends Rma
{
    public function __construct(
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\AddInterface $rmaAdd,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->rmaRepository = $rmaRepository;
        $this->rmaAdd = $rmaAdd;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('New RMA'));

        $data = $this->backendSession->getFormData(true);
        if ($ticketId = $this->getRequest()->getParam('ticket_id')) {
            $data['ticket_id'] = $ticketId;
        }

        $rma = $this->rmaRepository->create();
        if (!empty($data)) {
            $rma->setData($data);
        }

        $this->registry->register('current_rma', $rma);
        if ($orderId  = $this->getRequest()->getParam('order_id')) {
            $this->rmaAdd->initFromOrder($rma, $orderId);
            $this->_addContent($resultPage->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Rma\Edit'));
        } else {
            $this->_addContent($resultPage->getLayout()->getBlock('rma_adminhtml_rma_create'));
        }

        return $resultPage;
    }
}
