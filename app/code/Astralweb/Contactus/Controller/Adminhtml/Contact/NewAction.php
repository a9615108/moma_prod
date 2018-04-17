<?php

namespace Astralweb\Contactus\Controller\Adminhtml\Contact;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;

class NewAction extends Action
{

    protected $resultForwardFactory;

    public function __construct(Context $context, ForwardFactory $resultForwardFactory)
    {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Astralweb_Contactus::save');
    }

    public function execute()
    {

        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}