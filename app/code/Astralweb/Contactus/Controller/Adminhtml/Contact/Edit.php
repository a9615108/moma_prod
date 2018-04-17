<?php

namespace Astralweb\Contactus\Controller\Adminhtml\Contact;

class Edit extends \Magento\Backend\App\Action
{

    protected $_coreRegistry = null;

    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Astralweb_Contactus::save');
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Astralweb_Contactus::contactus_content')
            ->addBreadcrumb(__('Item'), __('Item'))
            ->addBreadcrumb(__('Manage Group Email Item'), __('Manage Group Email Item'));
        return $resultPage;
    }

    public function execute()
    {

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Astralweb\Contactus\Model\Contact');
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('contact_data', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('edit_item') : __('new_item'),
            $id ? __('Edit Email') : __('New Email')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Email'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Email'));
        return $resultPage;
    }
}