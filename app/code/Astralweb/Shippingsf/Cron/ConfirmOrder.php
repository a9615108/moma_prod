<?php
namespace Astralweb\Shippingsf\Cron;

use Magento\Sales\Api\OrderManagementInterface;

class ConfirmOrder
{
    protected $logger;
    protected $_resource;
    protected $_helper;
    protected $_orderRepository;
    protected $_convertOrder;
    protected $_trackFactory;
    protected $_shipmentNotifier;


    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        \Astralweb\Shippingsf\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier
    )
    {
        $this->logger = $loggerInterface;
        $this->orderManagement = $orderManagement;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->_helper = $helper;
        $this->_resource = $resource;
        $this->_orderRepository = $orderRepository;
        $this->_objectManager = $objectManager;
        $this->_convertOrder = $convertOrder;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_trackFactory = $trackFactory;
    }
    public function OrderSearch()
    {
        $datelog = date("Y-m-d h:i:sa", time());
        $urlapi = $this->_helper->getUrlApi();
        $checkHeader = $this->_helper->getCheckHeader();
        $checkBody = $this->_helper->getCheckBody();
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_shippingsf');
        $sql = "SELECT * FROM " . $tableName." WHERE status = 0";
        $result = $connection->fetchAll($sql);
        if(!empty($result)){
            foreach ($result as $item) {
                $orderId = $item['order_id'];
                $order = $this->_orderRepository->get($orderId);
                $orderIncrementId = $order->getIncrementId();
                $allItems = $order->getAllItems();
                $status = $order->getStatus();
                $request ='<?xml version="1.0" encoding="UTF-8" ?><Request service="OrderSearchService" lang="zh-CN"><Head>'.$checkHeader.'</Head><Body><OrderSearch orderid="'.$orderIncrementId.'" /></Body></Request>';
                $result = $this->_helper->ordersearch($orderIncrementId,$urlapi, $checkHeader, $checkBody);
                $xml = simplexml_load_string($result);
                $response = $xml->Head;
                if($response == 'OK'){
                    $orderResponse = $xml->Body->OrderResponse;
                    $trackingnumber = $orderResponse['mailno'];
                    $destcode = $orderResponse['destcode'];
                    $data = array(
                        'carrier_code' => 'custom',
                        'title' => 'SF Express',
                        'number' => $trackingnumber, // Replace with your tracking number
                    );
                    //Create shipment
                    if($status == 'holded'){
                        $order->unhold();
                    }
                    $shipment = $this->_convertOrder->toShipment($order);
                    foreach ($allItems as $orderItem) {
                        if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                            continue;
                        }

                        $qtyShipped = $orderItem->getQtyToShip();
                        $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                        $shipment->addItem($shipmentItem);

                    }
                    $shipment->register();
                    $shipment->getOrder()->setIsInProcess(true);
                    try {

                        $track = $this->_trackFactory->create()->addData($data);
                        $shipment->addTrack($track);
                        $shipment->save();
                        $shipment->getOrder()->save();
                        // Send email
                        $this->_shipmentNotifier->notify($shipment);

                        $shipment->save();
                        $order->setStatus('shipping_processing');
                        $order->setState('complete');
                        $order->addStatusToHistory('shipping_processing', 'Change status shipping_processing sucess', false);
                        $order->save();

                        $this->_helper->WritelogSF($datelog,$orderIncrementId,'ordersearch',$request,$result,'shipping_processing');
                        $sqlstatus = "UPDATE " . $tableName . " SET status = 1,mailno = ".$trackingnumber.",destcode = ".$destcode." WHERE order_id = ".$orderId;
                        $connection->query($sqlstatus);

                    } catch (\Exception $e) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                    }

                }else{
                    if($status != 'holded'){
                        $error = $xml->ERROR;
                        $order->setStatus('holded');
                        $order->hold();
                        $order->addStatusToHistory('holded', $error, false);
                        $order->save();
                        $this->_helper->WritelogSF($datelog,$orderIncrementId,'ordersearch',$request,$result,'holded');
                    }else{
                        $error = $xml->ERROR;
                        $order->addStatusToHistory('holded', $error, false);
                        $order->save();
                        $this->_helper->WritelogSF($datelog,$orderIncrementId,'ordersearch',$request,$result,'holded');
                    }
                    $sqlstatus = "UPDATE " . $tableName . " SET status = 0 WHERE order_id = ".$orderId;
                    $connection->query($sqlstatus);
                }


            }
        }




    }
    public function Confirm()
    {
        $datelog = date("Y-m-d h:i:sa", time());
        $urlapi = $this->_helper->getUrlApi();
        $checkHeader = $this->_helper->getCheckHeader();
        $checkBody = $this->_helper->getCheckBody();
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_shippingsf');
        $sql = "SELECT * FROM " . $tableName." WHERE status = 1";
        $result = $connection->fetchAll($sql);
        if(!empty($result)) {
            foreach ($result as $item) {
                $orderId = $item['order_id'];
                $order = $this->_orderRepository->get($orderId);
                $status = $order->getStatus();
                $orderIncrementId = $order->getIncrementId();
                $mailno = $item['mailno'];
                // $return_tracking = $item['return_tracking'];
                $request ='<?xml version="1.0" encoding="UTF-8" ?><Request service="OrderConfirmService" lang="zh-CN"><Head>'.$checkHeader.'</Head><Body><OrderConfirm orderid="'.$orderIncrementId.'" mailno="'.$mailno.'" dealtype="1"  />
<OrderConfirmOption weight="1" volume="20" /></OrderConfirm></Body></Request>';
                $result = $this->_helper->confirmorder($orderIncrementId, $mailno, '1', '1', '20',$urlapi, $checkHeader, $checkBody);
                $xml = simplexml_load_string($result);
                $response = $xml->Head;
                //$response == 'OK';
                if ($response == 'OK') {
                    $order->setStatus('delivering');
                    $order->setState('complete');
                    $order->addStatusToHistory('delivering', 'Change status delivering sucess', false);
                    $order->save();
                    //Update status on table astralweb_shippingsf
                    //Write Log
                    $this->_helper->WritelogSF($datelog,$orderIncrementId,'Confirm',$request,$result,'delivering');

                    $sqlstatus = "UPDATE " . $tableName . " SET status = 2 WHERE order_id = " . $orderId;
                    $connection->query($sqlstatus);

                } else {
                    $error = $xml->ERROR;
                    $order->addStatusToHistory($status, $error, false);
                    $order->save();
                    //Write Log
                    $this->_helper->WritelogSF($datelog,$orderIncrementId,'Confirm',$request,$result,$status);
                }

            }
        }


    }
    public function Route(){
        $datelog = date("Y-m-d h:i:sa", time());
        $urlapi = $this->_helper->getUrlApi();
        $checkHeader = $this->_helper->getCheckHeader();
        $checkBody = $this->_helper->getCheckBody();
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_shippingsf');
        $sql = "SELECT * FROM " . $tableName." WHERE status = 2";
        $result = $connection->fetchAll($sql);
        if(!empty($result)) {
            foreach ($result as $item) {
                $trackingNumber = $item['mailno'];
                $orderId = $item['order_id'];
                $order = $this->_orderRepository->get($orderId);
                $orderIncrementId = $order->getIncrementId();
                $status = $order->getStatus();
                $request = '<?xml version="1.0" encoding="UTF-8" ?><Request service="RouteService" lang="zh-CN"><Head>'.$checkHeader.'</Head><Body><RouteRequest tracking_type="'.'1'.'" method_type="'.'1'.'" tracking_number="'.$trackingNumber.'" />
     </RouteRequest></Body></Request>';
                $result = $this->_helper->RouteService('1','1',$trackingNumber,$urlapi, $checkHeader, $checkBody);
                $xml = simplexml_load_string($result);
                $response = $xml->Head;
                if($response == 'OK'){
                    if(!empty($xml->Body)){
                        $Routeresponse = $xml->Body->RouteResponse;
                        //Get data Route to Save
                        $routes = $xml->xpath('//Route');
                        foreach ($routes as $route){
                            $route = (array)$route;
                            $data = $route['@attributes'];
                            $routesave[] = $data;
                        }
                        $outfinal = json_encode($routesave);

                        //Change Status and comment
                        $order->setStatus('complete');
                        $order->setState('complete');
                        $order->addStatusToHistory('complete', 'Change status complete sucess', false);
                        $order->save();
                        //Write Log
                        $this->_helper->WritelogSF($datelog,$orderIncrementId,'RouteService',$request,$result,'complete');


                        //Save data route and status
                        $sqlstatus = "UPDATE " . $tableName . " SET status = 3,route_tracking ='".$outfinal. "' WHERE order_id = " . $orderId;
                        $connection->query($sqlstatus);

                    }

                }else{
                    $error = $xml->ERROR;
                    $order->addStatusToHistory($status, $error, false);
                    $order->save();
                    $this->_helper->WritelogSF($datelog,$orderIncrementId,'RouteService',$request,$result,$status);
                }

            }
        }

    }

}