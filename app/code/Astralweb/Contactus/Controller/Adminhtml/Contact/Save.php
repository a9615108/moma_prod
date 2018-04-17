<?php

namespace Astralweb\Contactus\Controller\Adminhtml\Contact;

use Magento\Backend\App\Action;

class Save extends Action {

    protected $_fileUploaderFactory;
    protected $filesystem;

    public function __construct(\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, Action\Context $context, \Magento\Framework\Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Astralweb_Contactus::save');
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $model = $this->_objectManager->create('Astralweb\Contactus\Model\Contact');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }


            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this item.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e, __('Something went wrong while saving item.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

}