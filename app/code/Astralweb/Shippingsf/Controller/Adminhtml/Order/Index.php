<?php
namespace Astralweb\Shippingsf\Controller\Adminhtml\Order;


use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $_orderAction;
    protected $_csvProcessor;
    protected $_order;
    protected $_helperData;
    protected $_convertOrder;
    protected $_orderShipmentTrackingModel;
    protected $_shipmentNotifier;
    protected $_trackFactory;
    protected $_resource;
    protected $_moduleManager;
    protected $_helperDataCvs;



    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param GetCollection $orderAction
     */
    public function __construct(Context $context, PageFactory $resultPageFactory,\Magento\Framework\File\Csv $csvProcessor,
                                \Magento\Framework\App\ResourceConnection $resource,
                                \Magento\Sales\Model\Order\Shipment\Track $orderShipmentTrackingModel,\Magento\Sales\Api\Data\OrderInterface $order,\Astralweb\Shippingsf\Helper\Data $helperData,\Magento\Sales\Model\Convert\Order $convertOrder,
                                \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,\Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
                                \Astralweb\Shippingcvs\Helper\Data $helperDataCvs,
                                \Magento\Framework\Module\Manager $moduleManager

    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_csvProcessor = $csvProcessor;
        $this->_helperData = $helperData;
        $this->_convertOrder = $convertOrder;
        $this->_order = $order;
        $this->_trackFactory = $trackFactory;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_orderShipmentTrackingModel =$orderShipmentTrackingModel;
        $this->_resource = $resource;
        $this->_moduleManager = $moduleManager;
        $this->_helperDataCvs = $helperDataCvs;
        parent::__construct($context);
    }



    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $resultPage = $this->resultPageFactory->create();

        $form_key = $this->getRequest()->getParam("form_key");
        $orderError = array();

        if($form_key) {
            if (isset($_FILES['file'])) {
                if (!isset($_FILES['file']['tmp_name'])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
                }
                $importProductRawData = $this->_csvProcessor->getData($_FILES['file']['tmp_name']);
                foreach ($importProductRawData as $rowIndex => $dataRow) {

                    if ($dataRow[0] == 'order_increment') continue;
                    $order = $this->_order->loadByIncrementId($dataRow[0]);
                    $datelog = date("Y-m-d h:i:sa", time());
                    if(count($order->getData()) == 0){ $orderError[] = $dataRow[0];}
                    if (count($order->getData()) > 0) {
                        $Incrementid = $order->getIncrementId();
                        $orderId = $order->getId();
                        $allItems = $order->getAllItems();
                        $shippingAdress = $order->getShippingAddress();
                        $billingAddress = $order->getBillingAddress();

                        $shippingMethod = $order->getShippingMethod();
                        $status = $order->getStatus();
                        $state = $order->getState();
                        //var_dump($state);var_dump($status);die;
                        // var_dump($shippingMethod);
                        // var_dump($this->_moduleManager->isEnabled('Astralweb_Shippingcvs'));die;
                        //var_dump(strpos( strtolower($status), 'pending') !== false);die;
                        if($state =='complete' || (strpos( strtolower($status), 'pending') !== false) ){
                            $orderError[] = $dataRow[0];
                            continue;
                        }
                        //var_dump($orderError);die;
                        if ($shippingMethod == 'collect_store_collect_store') {

                            // Post Api
                            $urlapi = $this->_helperData->getUrlApi();
                            $checkheader = $this->_helperData->getCheckHeader();
                            $checkbody = $this->_helperData->getCheckBody();
                            $j_company = $this->_helperData->getCompany();
                            $j_contact = $this->_helperData->getContact();
                            $j_telphone = $this->_helperData->getTel();
                            $j_address = $this->_helperData->getAdress();
                            $j_province = $this->_helperData->getProvince();
                            $j_city = $this->_helperData->getCity();
                            //$d_company = $shippingAdress->getData('company');
                            $d_contact = $shippingAdress->getData('firstname') . ' ' . $shippingAdress->getData('lastname');
                            $d_telphone = $shippingAdress->getData('telephone');
                            $d_address = $shippingAdress->getData('street');
                            $d_province = $shippingAdress->getData('region');
                            $d_city = $shippingAdress->getData('city');
                            $custid = $this->_helperData->getCreditAccount();
                            $creditcardno = $this->_helperData->getCreditNo();
                            $stringXmlCargo = '';
                            $stringXmlAddServices = '';
                            $currency = $order->getOrderCurrencyCode();
                            $source_area = '';
                            foreach ($allItems as $item) {
                                if($item->getData('product_type') == 'simple'){
                                    $count = $item->getData('qty_ordered');
                                    $unit = '';
                                    $weight = $item->getData('weight');
                                    $amount = $item->getData('price');
                                    $stringXmlCargo .= '<Cargo name="'.$item->getData('name').'" count="'.$count.'" unit="'.$unit.'" weight="'.$weight.'" amount="'.$amount.'" currency="'.$currency.'" source_area="'.$source_area.'"></Cargo>';
                                }


                            }
                            $payment = $order->getPayment();
                            $paymentMethod = $payment->getMethodInstance()->getCode();
                            if($paymentMethod == 'cashondelivery'){
                                $pay_method = 1;
                            }elseif ($paymentMethod == 'taixinbank') {
                                $pay_method = 1;
                            }
                            $totalAmount =  $order->getGrandTotal();
                            if($paymentMethod == 'cashondelivery'){
                                $stringXmlAddServices .= '<AddedService name="COD" value="'.$totalAmount.'" value1="'.$creditcardno.'"></AddedService>';
                            }


                            $j_shippercode  = $this->_helperData->citycode($j_province,$j_city);
                            $d_deliverycode = $this->_helperData->citycode($d_province,$d_city);
                            $request ='<?xml version="1.0" encoding="UTF-8" ?><Request service="OrderService" lang="zh-CN"><Head>'.$checkheader.'</Head><Body><Order orderid="'.$Incrementid.'" express_type="1" j_company="'.$j_company.'" j_contact="'.$j_contact.'" j_tel="'.$j_telphone.'" j_address="'.$j_address.'" d_company="'.$d_contact.'" d_contact="'.$d_contact.'" d_tel="'.$d_telphone.'" d_address="'.$d_address.'" parcel_quantity="1" pay_method="'.$pay_method.'" custid="'.$custid.'" j_shippercode="'.$j_shippercode.'" d_deliverycode="'.$d_deliverycode.'" cargo_total_weight="" sendstarttime="" mailno="'.''.'" remark="" is_gen_bill_no="1" >'.$stringXmlCargo.$stringXmlAddServices.'</Order></Body></Request>';

                            $result = $this->_helperData->orderservice($Incrementid, $j_company, $j_contact, $j_telphone, $j_address, $d_contact, $d_contact, $d_telphone, $d_address, $pay_method, $d_province, $d_city, $j_province, $j_city, $custid, "", $urlapi, $checkheader, $checkbody,$stringXmlCargo,$stringXmlAddServices);

                            if ($result == '') {

                                $connection = $this->_resource->getConnection();
                                $tableName = $this->_resource->getTableName('astralweb_shippingsf');
                                $sqlSelect =  "SELECT * FROM " . $tableName." WHERE status = 0 AND order_id =".$orderId;
                                $resultData = $connection->fetchAll($sqlSelect);
                                if(count($resultData) == 0){
                                    $sql = "INSERT INTO " . $tableName . " (order_id , mailno, return_tracking, status, route_tracking, destcode) VALUES ($orderId,'','','0','','')";
                                    $connection->query($sql);
                                }

                                continue;

                            }
                            $xml = simplexml_load_string($result);
                            //var_dump($xml);die;
                            $response = $xml->Head;
                            if ($response == 'OK') {
                                //Get tracking number
                                $orderResponse = $xml->Body->OrderResponse;
                                // $returnTracking = $orderResponse['return_tracking_no'];
                                $trackingnumber = $orderResponse['mailno'];
                                $destcode = $orderResponse['destcode'];

                                // $orderIdResponse = $orderResponse['orderid'];
                                $data = array(
                                    'carrier_code' => 'custom',
                                    'title' => 'SF Express',
                                    'number' => $trackingnumber, // Replace with your tracking number
                                );
                                //Save information response to table astralweb_shippingsf
                                $connection = $this->_resource->getConnection();
                                $tableName = $this->_resource->getTableName('astralweb_shippingsf');
                                $sqlSelect =  "SELECT * FROM " . $tableName." WHERE status = 0 AND order_id =".$orderId;
                                $resultData = $connection->fetchAll($sqlSelect);
                                if(count($resultData) > 0){
                                    $sqlupdate = "UPDATE " . $tableName . " SET status = 1 WHERE order_id = " . $orderId;
                                    $connection->query($sqlupdate);

                                }else{
                                    $sql = "INSERT INTO " . $tableName . " (order_id , mailno, return_tracking, status, route_tracking, destcode) VALUES ($orderId,$trackingnumber,'','1','','".$destcode."')";
                                    $connection->query($sql);
                                }

                                if($status == 'holded'){
                                    $order->unhold();
                                }
                                //Create Shipment
                                if (!$order->canShip()) {
                                    $orderError[] = $dataRow[0];
                                    continue;
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
                                    $this->_helperData->WritelogSF($datelog,$Incrementid,'orderservice',$request,$result,'shipping_processing');

                                } catch (\Exception $e) {
                                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                                }


                            } else {
                                if($status != 'holded'){
                                    $orderError[] = $dataRow[0];
                                    $error = $xml->ERROR;
                                    $order->setStatus('holded');
                                    $order->hold();
                                    $order->addStatusToHistory('holded', $error, false);
                                    $order->save();
                                    $this->_helperData->WritelogSF($datelog,$Incrementid,'orderservice',$request,$result,'holded');
                                }else{
                                    $orderError[] = $dataRow[0];
                                    $error = $xml->ERROR;
                                    $order->addStatusToHistory('holded', $error, false);
                                    $order->save();
                                    $this->_helperData->WritelogSF($datelog,$Incrementid,'orderservice',$request,$result,'holded');
                                }
                                                                $connection = $this->_resource->getConnection();

                                $tableNameSF = $this->_resource->getTableName('astralweb_shippingsf');
                                $sql = "INSERT INTO " . $tableNameSF . " (order_id , mailno, return_tracking, status, route_tracking, destcode) VALUES ($orderId,'','','0','','')";
                                $connection->query($sql);

                            }


                        }elseif ($this->_moduleManager->isEnabled('Astralweb_Shippingcvs') && $shippingMethod == 'collect_storecvs_collect_storecv'){
                            // var_dump($shippingAdress->getData('region'));die;
                            $connection = $this->_resource->getConnection();
                            $tableNameCVS = $this->_resource->getTableName('astralweb_shippingcvs');
                            $sql =  "SELECT cvsspot FROM " . $tableNameCVS ." WHERE increment_id = ".$Incrementid;
                            $result = $connection->fetchAll($sql);
                            $textSmsCreareShipment = $this->_helperDataCvs->getTextSmsCreateShipment();
                            $phoneBilling = $billingAddress->getData('telephone');
                            if(count($result) > 0){
                                $STNO = $result[0]['cvsspot'];

                                $shiptoName = $shippingAdress->getData('firstname').$shippingAdress->getData('lastname');
                                $shiptoPhone = $shippingAdress->getData('telephone');
                                $phoneBilling = $billingAddress->getData('telephone');
                                $countryId = $billingAddress->getData('country_id');
                                $nameStoreCVS = $shippingAdress->getData('region');
                                $prefixPhone =  $this->_helperDataCvs->getCountryCodePhone($countryId);
                                $phoneSMS = '+'.$prefixPhone.substr($phoneBilling,1);
                                if($phoneBilling !== ''){
                                    $threelastPhone = substr($phoneBilling,-3);
                                }else{
                                    $threelastPhone= '';
                                }
                                $storePhone = $this->_helperDataCvs->getStorePhone();
                                $subtotal = (int) $order->getGrandTotal();

                                $request = '<?xml version="1.0" encoding="UTF-8"?><ORDER_DOC><ORDER><ECNO>'.'248'.'</ECNO><ODNO>'.$Incrementid.'</ODNO><STNO>'.$STNO.'</STNO><AMT>0</AMT><CUTKNM>'.$shiptoName.'</CUTKNM><CUTKTL>'.$threelastPhone.'</CUTKTL><PRODNM>'.'0'.'</PRODNM><ECWEB><![CDATA[MOMA購物網]]></ECWEB><ECSERTEL>'.$storePhone.'</ECSERTEL>
<REALAMT>'.$subtotal.'</REALAMT><TRADETYPE>'.'3'.'</TRADETYPE><SERCODE>'.'990'.'</SERCODE><EDCNO>'.'D04'.'</EDCNO></ORDER><ORDERCOUNT><TOTALS>1</TOTALS></ORDERCOUNT></ORDER_DOC>';
                                $resultfinal = $this->_helperDataCvs->cvsapi($Incrementid);
                                if (strpos($resultfinal, '成功0筆(含異常0筆)，踼退1筆，合計1筆') !== false) {
                                    $resultfinal = str_replace("成功0筆(含異常0筆)，踼退1筆，合計1筆", "", $resultfinal);
                                }
                                if(strpos($resultfinal,'成功1筆(含異常0筆)，踼退0筆，合計1筆') !== false){
                                    $resultfinal = str_replace("成功1筆(含異常0筆)，踼退0筆，合計1筆", "", $resultfinal);

                                }
                                // var_dump($resultfinal);die;
                                //$resultfinal = str_replace("成功0筆(含異常0筆)，踼退1筆，合計1筆", "", $resultfinal);

                                $xml = simplexml_load_string($resultfinal);
                                $countError = $xml->RTN_RESULT->ERRCNT;
                                if ($countError[0] == "0") {
                                    if($status == 'holded'){
                                        $order->unhold();
                                    }
                                    //Create Shipment
                                    if (!$order->canShip()) {
                                        $orderError[] = $dataRow[0];
                                        continue;
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
                                        $shipment->save();
                                        $shipment->getOrder()->save();
                                        $this->_shipmentNotifier->notify($shipment);
                                        $shipment->save();
                                        $order->setStatus('delivering');
                                        $order->setState('complete');
                                        $order->addStatusToHistory('delivering', 'Change status delivering sucess', false);
                                        $order->save();
                                        //Update status =1 table shippingcvs
                                        $this->_helperDataCvs->WritelogCVS($datelog,$Incrementid,'Post system CVS',$request,$resultfinal,'delivering');

                                        $connection = $this->_resource->getConnection();
                                        $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
                                        $sqlstatus = "UPDATE " . $tableName . " SET status = 1 WHERE increment_id = " . $Incrementid;
                                        $connection->query($sqlstatus);
                                        $this->_helperDataCvs->SendSMSApi($textSmsCreareShipment,$phoneSMS);

                                    } catch (\Exception $e) {
                                        throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                                    }


                                } else {
                                    $orderError[] = $dataRow[0];
                                    $errors = $xml->xpath('//ERRDESC');
                                    $strError = '';
                                    foreach ($errors as $error) {
                                        $error = (array)$error;
                                        $strError .= $error[0];
                                    }
                                    if($status != 'holded'){
                                        $order->setStatus('holded');
                                        $order->hold();
                                        $order->addStatusToHistory('holded', $strError, false);
                                        $order->save();
                                        $this->_helperDataCvs->WritelogCVS($datelog,$Incrementid,'Post system CVS',$request,$resultfinal,'holded');
                                    }else{
                                        $order->addStatusToHistory('holded', $strError, false);
                                        $order->save();
                                        $this->_helperDataCvs->WritelogCVS($datelog,$Incrementid,'Post system CVS',$request,$resultfinal,'holded');

                                    }


                                }
                            }

                        }
                    }
                }
            }
            // var_dump($orderError);die;
            if(count($orderError) > 0){
                $orderErrorText = implode(",",$orderError);
                $this->getMessageManager()->addNotice(__("Order have Increment ID ".$orderErrorText." Error Shipment"));
            }else{
                $this->getMessageManager()->addSuccess(__("Orders submitted for shipment successfully!"));
            }
        }


        $resultPage->getConfig()->getTitle()->prepend(__('Shipping SF Import'));

        $resultPage->addBreadcrumb(__('AstralWeb'), __('Shipping SF Import'));
        return $resultPage;
    }
}
