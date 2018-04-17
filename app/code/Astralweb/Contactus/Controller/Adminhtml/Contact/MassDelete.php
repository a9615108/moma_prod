<?php

namespace Astralweb\Contactus\Controller\Adminhtml\Contact;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Astralweb\Contactus\Model\ResourceModel\Contact\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{

    protected $filter;
    protected $collectionFactory;

    public function __construct(Action\Context $context, CollectionFactory $collectionFactory, Filter $filter)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {

        $ids = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');
        if (!$ids && $excluded == 'false') {
            $collection = $this->collectionFactory->create();
            $collectionSize = $collection->getSize();

            foreach ($collection as $item) {
                $item->delete();
            }

            $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));
        } elseif ($ids) {
            try {
                foreach ($ids as $id) {
                    $row = $this->_objectManager->get('Astralweb\Contactus\Model\Contact')->load($id);
                    $row->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } elseif ($excluded) {
            $idArr = array();
            foreach ($excluded as $id) {
                $idArr[] = $id;
            }
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('id', array('nin' => $idArr));
            $collectionSize = $collection->getSize();

            foreach ($collection as $item) {
                $item->delete();
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');

    }

}