<?php
namespace Astralweb\Invoicetype\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Astralweb\Invoicetype\Model\InvoiceTypeFactory as InvoiceTypeFactory;

class SaveInvoice implements ObserverInterface
{

    protected $request;
    protected $_invoiceType;
     protected $_order;
    protected $logger;
    protected $_resource;
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        InvoiceTypeFactory $invoiceType,
          \Magento\Sales\Model\Order $order,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->request = $request;
        $this->_invoiceType = $invoiceType;
         $this->_resource = $resource;
        $this->logger = $logger;
        $this->_order = $order;
    }
    public function execute(Observer $observer)
    {
        $invoiceType = null;
        $taxId = null;
        $purchaserName = null;
        $order = $observer->getEvent()->getOrder();
        $IncrementId = $order->getEntityId();
        $status = $order->getStatus();
         $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_invoicetype');
        $sql = "SELECT * FROM " . $tableName." WHERE order_id = ".$IncrementId;
        $result = $connection->fetchAll($sql);
        $orderExsist =false;
        if(count($result) > 0){
            $orderExsist = true;
        }
        if(strpos($status, 'pending') > -1 && $orderExsist == false){
            $post1 = $this->request->getPost('invoice');
        $post2 = $this->request->getPost('invoice-select');
        $post3 = $this->request->getPost('purchaser-name');
        if(isset($post1)){
            if($post1 == 'on'){
                $invoiceType = 'three';
                $taxId = $post2;
            }else{
                $invoiceType = 'two';
                $purchaserName = $post3;
            }
            $invoiceTypeObject = $this->_invoiceType->create();

            $invoiceTypeObject->setData('order_id',$IncrementId);
            $invoiceTypeObject->setData('invoice_type',$invoiceType);
            $invoiceTypeObject->setData('purchaser_name',$taxId);
            $invoiceTypeObject->setData('tax_id',$purchaserName);
            $invoiceTypeObject->save();
        }
        }
        

    }
}
