<?php

namespace Astralweb\Contactus\Controller\Adminhtml\Contact;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Astralweb_Contactus::contactus_content');
        $resultPage->addBreadcrumb(__('email'), __('email'));
        $resultPage->addBreadcrumb(__('Manage email'), __('Manage email'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Email'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Astralweb_Contactus::contactus_content');
    }
}