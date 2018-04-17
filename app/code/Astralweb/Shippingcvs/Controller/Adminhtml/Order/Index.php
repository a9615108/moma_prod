<?php
namespace Astralweb\Shippingcvs\Controller\Adminhtml\Order;


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


    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param GetCollection $orderAction
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, \Magento\Framework\File\Csv $csvProcessor,
                                \Magento\Framework\App\ResourceConnection $resource,
                                \Magento\Sales\Model\Order\Shipment\Track $orderShipmentTrackingModel, \Magento\Sales\Api\Data\OrderInterface $order, \Astralweb\Shippingcvs\Helper\Data $helperData, \Magento\Sales\Model\Convert\Order $convertOrder,
                                \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory, \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier

    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_csvProcessor = $csvProcessor;
        $this->_helperData = $helperData;
        $this->_convertOrder = $convertOrder;
        $this->_order = $order;
        $this->_trackFactory = $trackFactory;
        $this->_messageManager = $messageManager;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_orderShipmentTrackingModel = $orderShipmentTrackingModel;
        $this->_resource = $resource;
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

        if ($form_key) {
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
                        $shippingMethod = $order->getShippingMethod();
                        $status = $order->getStatus();
                        $state = $order->getState();
                         if($status == 'holded' || $state =='complete'){
                            $orderError[] = $dataRow[0];
                            continue;
                        }
                        // Post Api

                        if ($shippingMethod == 'collect_storecvs_collect_storecv') {
                            $connection = $this->_resource->getConnection();
                            $tableNameCVS = $this->_resource->getTableName('astralweb_shippingcvs');
                            $sql =  "SELECT cvsspot FROM " . $tableNameCVS ." WHERE increment_id = ".$Incrementid;
                            $result = $connection->fetchAll($sql); 
                            $STNO = $result[0]['cvsspot']; 
                            $shiptoName = $shippingAdress->getData('firstname').$shippingAdress->getData('lastname');
                            $shiptoPhone = $shippingAdress->getData('telephone');
                             $threelastPhone = substr($shiptoPhone,-3);
                             $storePhone = $this->_helperData->getStorePhone();
                             $subtotal = (int) $order->getGrandTotal();

                            $request = '<?xml version="1.0" encoding="UTF-8"?>
                                            <ORDER_DOC>
                                                <ORDER>
                                                    <ECNO>'.'248'.'</ECNO>
                                                    <ODNO>'.$Incrementid.'</ODNO>
                                                    <STNO>'.$STNO.'</STNO>
                                                    <AMT>0</AMT>
                                                    <CUTKNM>'.$shiptoName.'</CUTKNM>
                                                    <CUTKTL>'.$threelastPhone.'</CUTKTL>
                                                    <PRODNM>'.'0'.'</PRODNM>
                                                    <ECWEB><![CDATA[MOMA購物網]]></ECWEB>
                                                    <ECSERTEL>'.$storePhone.'</ECSERTEL>
                                                    <REALAMT>'.$subtotal.'</REALAMT>
                                                    <TRADETYPE>'.'1'.'</TRADETYPE>
                                                    <SERCODE>'.'990'.'</SERCODE>
                                                    <EDCNO>'.'D04'.'</EDCNO>
                                                </ORDER>
                                                <ORDERCOUNT>
                                                    <TOTALS>1</TOTALS>
                                                </ORDERCOUNT>
                                            </ORDER_DOC>'; 

                            $resultfinal = $this->_helperData->cvsapi($Incrementid);
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
                                    $order->setStatus('delivering');
                                    $order->setState('complete');
                                    $order->addStatusToHistory('delivering', 'Change status delivering sucess', false);
                                    $order->save();

                                    $this->_helperData->WritelogCVS($datelog,$Incrementid,'Post system CVS',$request,$resultfinal,'delivering');
                                    //Update status =1 table shippingcvs
                                    $connection = $this->_resource->getConnection();
                                    $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
                                    $sqlstatus = "UPDATE " . $tableName . " SET status = 1 WHERE increment_id = " . $Incrementid;
                                    $connection->query($sqlstatus);

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

                                $order->setStatus('holded');
                                $order->hold();
                                $order->addStatusToHistory('holded', $strError, false);
                                $order->save();
                                $this->_helperData->WritelogCVS($datelog,$Incrementid,'Post system CVS',$request,$resultfinal,'holded');
                            }


                        }
                    }
                }
            }
            if(count($orderError) > 0){
                $orderErrorText = implode(",",$orderError);
                $this->getMessageManager()->addNotice(__("Order have Increment ID ".$orderErrorText." Error Shipment"));
            }else{
                $this->getMessageManager()->addSuccess(__("Orders submitted for shipment successfully!"));
            }
        }


            $resultPage->getConfig()->getTitle()->prepend(__('Shipping CVS Import'));

            $resultPage->addBreadcrumb(__('AstralWeb'), __('Shipping CVS Import'));
            return $resultPage;

    }
}