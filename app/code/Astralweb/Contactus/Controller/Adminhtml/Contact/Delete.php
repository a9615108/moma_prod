<?php

namespace Astralweb\Contactus\Controller\Adminhtml\Contactus;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Delete extends Action {

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Astralweb_Contactus::contact');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if($id) {
            try {
                $model = $this->_objectManager->create('Astralweb\Contactus\Model\Contact');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('This item has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete'));
        return $resultRedirect->setPath('*/*/');
    }
}