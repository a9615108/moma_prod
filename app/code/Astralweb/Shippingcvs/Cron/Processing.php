<?php
namespace Astralweb\Shippingcvs\Cron;

use Magento\Sales\Api\OrderManagementInterface;

class Processing
{

    protected $logger;
    protected $_resource;
    protected $_helper;
    protected $_orderRepository;
    protected $_directory;
    protected $_order;


    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        \Astralweb\Shippingcvs\Helper\Data $helper,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->logger = $loggerInterface;
        $this->orderManagement = $orderManagement;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->_helper = $helper;
        $this->_resource = $resource;
        $this->_orderRepository = $orderRepository;
        $this->_objectManager = $objectManager;
        $this->_order = $order;
    }
    public function ConfirmF04(){
        $datelog = date("Y-m-d h:i:sa", time());
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
        $filename = $this->_helper->getFileF04();
        if(filesize($filename)){
        $xml = simplexml_load_file($filename);
        if(count($xml->F04CONTENT) > 0){
            $orders = $xml->xpath('//ODNO');
            $orderSuccess = array();
            foreach ($orders as $order) {
                $order = (array)$order;
                $orderSuccess[] = $order[0];
            }
            foreach ($orderSuccess as $orderIncrement){


                $IncrementId = substr($orderIncrement,1);
                $order = $this->_order->loadByIncrementId($IncrementId);
                if(count($order->getData()) > 0 ){
                    $billingAddress = $order->getBillingAddress();
                                            $shippingAdress = $order->getShippingAddress();
                                                     $nameStoreCVS = $shippingAdress->getData('region');
                           
                    $phoneBilling = $billingAddress->getData('telephone');
                    $countryId = $billingAddress->getData('country_id');
                    $prefixPhone =  $this->_helper->getCountryCodePhone($countryId);
                    $phoneSMS = '+'.$prefixPhone.substr($phoneBilling,1);
                    $textSmsArrived = $this->_helper->getTextSmsPackageArrived($nameStoreCVS);
                    $sqlSelectStatus = "SELECT * FROM " . $tableName." WHERE increment_id = ". $IncrementId;
                    $result = $connection->fetchAll($sqlSelectStatus);
                    if(count($result) > 0){
                        if($result[0]['status'] == 1){
                            $order->setStatus('store_arrived');
                            $order->setState('complete');
                            $order->addStatusToHistory('store_arrived', 'Change status store_arrived sucess', false);
                            $order->save();
                            $this->_helper->WritelogCVS($datelog,$IncrementId,'ConfirmF04','','','store_arrived');
                            //  Update status =2

                            $sqlstatus = "UPDATE " . $tableName . " SET status = 2 WHERE increment_id = " . $IncrementId;
                            $connection->query($sqlstatus);
                            // Send SMS 
                            $this->_helper->SendSMSApi($textSmsArrived,$phoneSMS);
                        }
                    }
                    


                }

            }

        }
    }
    }
        public function SendSMSThreeDayArrived(){
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
        $sql = "SELECT * FROM " . $tableName." WHERE status = 2";
        $result = $connection->fetchAll($sql);
        $dateCurrent = date('m-d-Y');
        if(count($result) > 0){
            foreach ($result as $item) {
                $order = $this->_order->loadByIncrementId($item['increment_id']);
                $status = $order->getStatus();
                if($status == 'store_arrived'){
                    $orderId = $order->getData('entity_id');
                    $tableNameComment = $this->_resource->getTableName('sales_order_status_history');
                    $sqlComment = "SELECT * FROM " . $tableNameComment." WHERE parent_id = ".$orderId." AND status = 'store_arrived'";
                    $resultComment = $connection->fetchAll($sqlComment);
                    if(count($resultComment) > 0) {
                        $data  = end($resultComment);
                        $dateCreate = $data['created_at'];
                        $newDate = date("m-d-Y", strtotime($dateCreate));
                        $date1 = str_replace('-', '/', $newDate);
                        $thressDay = date('m-d-Y',strtotime($date1 . "+3 days"));
                        if($thressDay == $dateCurrent){
                            $billingAddress = $order->getBillingAddress();
                               $shippingAdress = $order->getShippingAddress();
                                                     $nameStoreCVS = $shippingAdress->getData('region');

                            $phoneBilling = $billingAddress->getData('telephone');
                            $countryId = $billingAddress->getData('country_id');
                            $prefixPhone =  $this->_helper->getCountryCodePhone($countryId);
                            $phoneSMS = '+'.$prefixPhone.substr($phoneBilling,1);
                            $textSmsPackageThree = $this->_helper->getTextSmsPackageThree($nameStoreCVS);
                            //Send SMS
                            $this->_helper->SendSMSApi($textSmsPackageThree,$phoneSMS);
                            //Change status order is shipping_processing
                            $order->setStatus('shipping_processing');
                            $order->setState('complete');
                            $order->addStatusToHistory('shipping_processing', 'Change status shipping_processing sucess', false);
                            $order->save();
                            //Update status table astralweb_shippingcvs is 6

                            $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
                            $sqlstatus = "UPDATE " . $tableName . " SET status = 6 WHERE increment_id = " . $item['increment_id'];
                            $connection->query($sqlstatus);


                        }
                    }
                }

                }
        }
    }
    public function ConfirmF05(){
        $datelog = date("Y-m-d h:i:sa", time());
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
        $filename = $this->_helper->getFileF05();
                if(filesize($filename)){

        $xml = simplexml_load_file($filename);

        $contentsF05 = $xml->xpath('F05CONTENT');
        if(count($contentsF05) > 0){
            foreach ($contentsF05 as $content){
                $BC1 = $content->BC1;
                $BC2 = $content->BC2;
                $BC1sub = substr($BC1,4,2);
                $BC2sub = substr($BC2,0,8);
                $IncrementId = $BC1sub.$BC2sub;
                $order = $this->_order->loadByIncrementId($IncrementId);
                if(count($order->getData()) > 0){
                    $sqlSelectStatus = "SELECT * FROM " . $tableName." WHERE increment_id = ". $IncrementId;
                    $result = $connection->fetchAll($sqlSelectStatus);
                    if(count($result) > 0){
                        if($result[0]['status'] ==2){
                            $order->setStatus('complete');
                            $order->setState('complete');
                            $order->addStatusToHistory('complete', 'Change status complete sucess', false);
                            $order->save();
                            $this->_helper->WritelogCVS($datelog,$IncrementId,'ConfirmF05','','','complete');
                            //Update status=3
                            $connection = $this->_resource->getConnection();
                            $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
                            $sqlstatus = "UPDATE " . $tableName . " SET status = 3, bc1 = ".$BC1.", bc2 = ".$BC2." WHERE increment_id = " . $IncrementId;
                            $connection->query($sqlstatus);

                        }
                    }


                }

            }
        }
    }

    }
    public function ConfirmF07(){
        $datelog = date("Y-m-d h:i:sa", time());
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
        $filename = $this->_helper->getFileF07();
                if(filesize($filename)){

        $xml = simplexml_load_file($filename);
        $contentsF07 = $xml->xpath('F07CONTENT');
        if(count($contentsF07) > 0){
            foreach ($contentsF07 as $content){
                $IncrementId = substr($content->ODNO,1);
                $errorCode = $content->RET_M;
                $order = $this->_order->loadByIncrementId($IncrementId);
                if(count($order->getData()) > 0) {
                    $sqlSelectStatus = "SELECT * FROM " . $tableName." WHERE increment_id = ". $IncrementId;
                    $result = $connection->fetchAll($sqlSelectStatus);
                    if(count($result) >0 ){
                        if($result[0]['status'] !=4){
                            $order->setStatus('holded');
                            $order->setState('holded');
                            $order->hold();
                            $strError = $this->getTextError($errorCode);
                            $order->addStatusToHistory('holded', $strError, false);
                            $order->save();
                            $this->_helper->WritelogCVS($datelog,$IncrementId,'ConfirmF07','','','holded');
                            //  Update status =4
                            $sqlstatus = "UPDATE " . $tableName . " SET status = 4 WHERE increment_id = " . $IncrementId;
                            $connection->query($sqlstatus);
                        }
                    }

                }

            }
        }

    }
    }
    public function ConfirmF09(){
        $datelog = date("Y-m-d h:i:sa", time());
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
        $filename = $this->_helper->getFileF09();
                if(filesize($filename)){

        $xml = simplexml_load_file($filename);
        $contentsF09 = $xml->xpath('F09CONTENT');
        if(count($contentsF09) > 0){
            foreach ($contentsF09 as $content){
                $IncrementId = substr($content->ODNO,1);
                $errorCode = $content->RET_R;
                $order = $this->_order->loadByIncrementId($IncrementId);
                if(count($order->getData()) > 0) {
                    $sqlSelectStatus = "SELECT * FROM " . $tableName." WHERE increment_id = ". $IncrementId;
                    $result = $connection->fetchAll($sqlSelectStatus);
                    if(count($result) > 0){
                        if($result[0]['status'] !=5){
                            $order->setStatus('holded');
                            $order->setState('holded');
                            $order->hold();
                            $strError = $this->getTextError($errorCode);
                            $order->addStatusToHistory('holded', $strError, false);
                            $order->save();
                            $this->_helper->WritelogCVS($datelog,$IncrementId,'ConfirmF09','','','holded');
                            //  Update status =5
                            $sqlstatus = "UPDATE " . $tableName . " SET status = 5 WHERE increment_id = " . $IncrementId;
                            $connection->query($sqlstatus);
                        }
                    }

                }

            }
        }

}
    }
    public function getTextError($codeError){
        switch ($codeError) {
            case "T00":
                return "General  acceptance rejection";
                break;
            case "T01":
                return "Store is closed, renovation, has no DCRONO";
                break;
            case "T02":
                return "No order purchase data";
                break;
            case "T03":
                return "Barcode erro";
                break;
            case "T04":
                return "barcode is duplicate";
                break;
            case "T05":
                return "abnormal situation";
                break;
            case "T06":
                return "System cancelled for over 30days";
                break;
            case "T08":
                return "Over size";
                break;
            case "D04":
                return "Improper packing of Widenation package(leakage)";
                break;
            case "S04":
                return "Order deleted by route";
                break;
            case "S05":
                return "Abonormal data";
                break;
            case "S06":
                return "Damaged packagingby reginal distribution";
                break;
            case "S07":
                return "Improper packing that informedby converience store.(leakage)";
                break;
            case "D01":
                return "Good lost by widenation distribution";
                break;
            case "D02":
                return "Non-delivery with regional distribution";
                break;
            case "S03":
                return "Lost by reginal distribution";
                break;
            case "N05":
                return "Lost by converience store";
                break;
            default:
                return "Error system CVS Shipping";
        }


    }





}
