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


namespace Mirasvit\Rma\Controller\Adminhtml\Report;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class Reason extends Action
{
    public function __construct(
        \Mirasvit\Report\Api\Repository\ReportRepositoryInterface $reportRepository,
        Registry $registry,
        Context $context
    ) {
        $this->reportRepository = $reportRepository;
        $this->registry         = $registry;
        $this->context          = $context;
        $this->backendSession   = $context->getSession();

        parent::__construct($context);
    }
    /**
     * @param \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage
     * @return \Magento\Backend\Model\View\Result\Page\Interceptor
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Rma::rma_report');
        $resultPage->getConfig()->getTitle()->prepend(__('Rma'));
        $resultPage->getConfig()->getTitle()->prepend(__('Reason Reports'));
        return $resultPage;
    }
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->registry->register('current_report', $this->reportRepository->get('rma_reasons'));
        /** @var \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->initPage($resultPage);
        return $resultPage;
    }
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Rma::rma_report');
    }
}