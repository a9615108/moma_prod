<?php

namespace Astralweb\ChangeOrderStatusGrid\Controller\Adminhtml\Order\ChangeStatus;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

class ChangeStatusGrid extends Action
{

    protected $filter;
    protected $_order;
    protected $_statusCollection;


    public function __construct(Action\Context $context,Order $order,CollectionFactory $statusCollection)
    {
        parent::__construct($context);
        $this->_order=$order;
        $this->_statusCollection=$statusCollection;

    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $paramsArray=$this->getRequest()->getParams();
        $ids = isset($paramsArray['filters']['increment_id']) ? $paramsArray['filters']:$this->getRequest()->getParam('selected') ;
        unset($ids['placeholder']);
        unset($ids['created_at']);
        $status = $this->getRequest()->getParam('status');
//        $statusCollection=$this->_statusCollection->create()->load();
//        $statusCollection->addFieldToFilter('status',$status);
//        $statusLabel=$statusCollection->getData()[0]['label'];

        $statusOfState=array();
        $isSuccess=false;
        if ($ids) {
            try {
                foreach ($ids as $id) {
                    $order = isset($paramsArray['filters']['increment_id']) ? $this->_order->loadByIncrementId($id) :$this->_order->load($id) ;
                    $statusCurrent=$order->getStatus();
                    $stateCurrent=$order->getState();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    $tableName = $resource->getTableName('sales_order_status_state'); //gives table name with prefix

                    $sql = "Select status  FROM " . $tableName ." Where state = '".$stateCurrent."'";
                    $result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
                    foreach ($result as $value) {
                        $statusOfState[]=$value['status'];
                    }

                    if (in_array($status, $statusOfState)){
                        $order->setStatus($status);
                        $order->setState($stateCurrent);
                        $order->save();
                        $isSuccess=true;
                    }else{
                        $isSuccess=false;
                        break;
                    }
                }
                if($isSuccess){
                    $this->messageManager->addSuccess(
                        __('The selected order  has been change status')
                    );
                }else{
                    $this->messageManager->addError(
                        __('The status does not following the orders states rules')
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales/order/');
    }

}