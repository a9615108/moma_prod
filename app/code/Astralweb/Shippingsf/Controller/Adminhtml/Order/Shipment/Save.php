<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Astralweb\Shippingsf\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;

/**
 * Class Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface
     */
    private $shipmentValidator;



    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helperData = $objectManager->get('Astralweb\Shippingsf\Helper\Data');
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($this->getRequest()->getParam('order_id'));
        $datelog = date("Y-m-d h:i:sa", time());
        $Incrementid = $order->getIncrementId();
        $orderId = $order->getId();
        $shippingAdress = $order->getShippingAddress();
        $allItems = $order->getAllItems();
        $shippingMethod = $order->getShippingMethod();
        if ($shippingMethod == 'collect_store_collect_store') {
            $urlapi = $helperData->getUrlApi();
            $checkheader = $helperData->getCheckHeader();
            $checkbody = $helperData->getCheckBody();
            $j_company = $helperData->getCompany();
            $j_contact = $helperData->getContact();
            $j_telphone = $helperData->getTel();
            $j_address = $helperData->getAdress();
            $j_province = $helperData->getProvince();
            $j_city =$helperData->getCity();
            $d_company = $shippingAdress->getData('company');
            $d_contact = $shippingAdress->getData('firstname') . ' ' . $shippingAdress->getData('lastname');
            $d_telphone = $shippingAdress->getData('telephone');
            $d_address = $shippingAdress->getData('street');
            $d_province = $shippingAdress->getData('region');
            $d_city = $shippingAdress->getData('city');
            $custid = $helperData->getCreditAccount();
            $creditcardno =  $helperData->getCreditNo();
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
                            $totalAmount = (int) $order->getGrandTotal();
                            if($paymentMethod == 'cashondelivery'){
                                $stringXmlAddServices .= '<AddedService name="COD" value="'.$totalAmount.'" value1="'.$creditcardno.'"></AddedService>';
                            }


                            $j_shippercode  = $helperData->citycode($j_province,$j_city);
                              $d_deliverycode = $helperData->citycode($d_province,$d_city);
                            $request ='<?xml version="1.0" encoding="UTF-8" ?><Request service="OrderService" lang="zh-CN"><Head>'.$checkheader.'</Head><Body><Order orderid="'.$orderId.'" express_type="1" j_company="'.$j_company.'" j_contact="'.$j_contact.'" j_tel="'.$j_telphone.'" j_address="'.$j_address.'" d_company="'.$d_company.'" d_contact="'.$d_contact.'" d_tel="'.$d_telphone.'" d_address="'.$d_address.'" parcel_quantity="1" pay_method="'.$pay_method.'" custid="'.$custid.'" j_shippercode="'.$j_shippercode.'" d_deliverycode="'.$d_deliverycode.'" cargo_total_weight="" sendstarttime="" mailno="'.''.'" remark="" is_gen_bill_no="1" >'.$stringXmlCargo.$stringXmlAddServices.'</Order></Body></Request>';

            $result = $helperData->orderservice($Incrementid, $j_company, $j_contact, $j_telphone, $j_address, $d_company, $d_contact, $d_telphone, $d_address, $pay_method, '北京', $d_city, $j_province, $j_city, $custid, "",$urlapi,$checkheader,$checkbody,$stringXmlCargo,$stringXmlAddServices);
            if($result == ''){
                $this->messageManager->addError(__('Please check configuation API'));
                $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
            }
                $xml = simplexml_load_string($result);
            $response = $xml->Head;
            if ($response == 'OK') {
                //Get tracking number
                $orderResponse = $xml->Body->OrderResponse;
               // $returnTracking = $orderResponse['return_tracking_no'];
                $trackingnumber = $orderResponse['mailno'];
                $destcode = $orderResponse['destcode'];



                $dataTracking[1] = array(
                    'carrier_code' => 'custom',
                    'title' => 'SF Express',
                    'number' => $trackingnumber, // Replace with your tracking number
                );
                //Save information response to table astralweb_shippingsf
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager

                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('astralweb_shippingsf');
               $sql = "INSERT INTO " . $tableName . " (order_id , mailno, return_tracking, status, route_tracking, destcode) VALUES ($orderId,$trackingnumber,'','1','','".$destcode."')";
                $connection->query($sql);   
               


                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
                $isPost = $this->getRequest()->isPost();
                if (!$formKeyIsValid || !$isPost) {
                    $this->messageManager->addError(__('We can\'t save the shipment right now.'));
                    return $resultRedirect->setPath('sales/order/index');
                }

                $data = $this->getRequest()->getParam('shipment');

                if (!empty($data['comment_text'])) {
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setCommentText($data['comment_text']);
                }

                $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

                try {
                    $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
                    $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
                    $this->shipmentLoader->setShipment($data);
                    $this->shipmentLoader->setTracking($dataTracking);
                    $shipment = $this->shipmentLoader->load();
                    if (!$shipment) {
                        $this->_forward('noroute');
                        return;
                    }

                    if (!empty($data['comment_text'])) {
                        $shipment->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );

                        $shipment->setCustomerNote($data['comment_text']);
                        $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                    }
                    $validationResult = $this->getShipmentValidator()
                        ->validate($shipment, [QuantityValidator::class]);

                    if ($validationResult->hasMessages()) {
                        $this->messageManager->addError(
                            __("Shipment Document Validation Error(s):\n" . implode("\n", $validationResult->getMessages()))
                        );
                        $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                        return;
                    }
                    $shipment->register();

                    $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                    $responseAjax = new \Magento\Framework\DataObject();

                    if ($isNeedCreateLabel) {
                        $this->labelGenerator->create($shipment, $this->_request);
                        $responseAjax->setOk(true);
                    }

                    $this->_saveShipment($shipment);
                    $order->setStatus('shipping_processing');
                    $order->setState('complete');
                    $order->addStatusToHistory('shipping_processing', 'Change status shipping_processing sucess', false);
                    $order->save();
                    $helperData->WritelogSF($datelog,$Incrementid,'orderservice',$request,$result,'shipping_processing');
                    if (!empty($data['send_email'])) {
                        $this->shipmentSender->send($shipment);
                    }

                    $shipmentCreatedMessage = __('The shipment has been created.');
                    $labelCreatedMessage = __('You created the shipping label.');

                    $this->messageManager->addSuccess(
                        $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
                    );
                    $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    if ($isNeedCreateLabel) {
                        $responseAjax->setError(true);
                        $responseAjax->setMessage($e->getMessage());
                    } else {
                        $this->messageManager->addError($e->getMessage());
                        $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                    }
                } catch (\Exception $e) {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    if ($isNeedCreateLabel) {
                        $responseAjax->setError(true);
                        $responseAjax->setMessage(__('An error occurred while creating shipping label.'));
                    } else {
                        $this->messageManager->addError(__('Cannot save shipment.'));
                        $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                    }
                }
                if ($isNeedCreateLabel) {
                    $this->getResponse()->representJson($responseAjax->toJson());
                } else {
                    $this->_redirect('sales/order/view', ['order_id' => $shipment->getOrderId()]);
                }


            }else{
                $error = $xml->ERROR;
                $order->setStatus('holded');
                $order->hold();
                $order->addStatusToHistory('holded', $error, false);
                $order->save();
                 $helperData->WritelogSF($datelog,$Incrementid,'orderservice',$request,$result,'holded');
                $this->_redirect('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id') ]);
                return;
            }


        }else {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
            $isPost = $this->getRequest()->isPost();
            if (!$formKeyIsValid || !$isPost) {
                $this->messageManager->addError(__('We can\'t save the shipment right now.'));
                return $resultRedirect->setPath('sales/order/index');
            }

            $data = $this->getRequest()->getParam('shipment');

            if (!empty($data['comment_text'])) {
                $this->_objectManager->get('Magento\Backend\Model\Session')->setCommentText($data['comment_text']);
            }

            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            try {
                $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
                $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
                $this->shipmentLoader->setShipment($data);
                $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
                $shipment = $this->shipmentLoader->load();
                if (!$shipment) {
                    $this->_forward('noroute');
                    return;
                }

                if (!empty($data['comment_text'])) {
                    $shipment->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $shipment->setCustomerNote($data['comment_text']);
                    $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }
                $validationResult = $this->getShipmentValidator()
                    ->validate($shipment, [QuantityValidator::class]);

                if ($validationResult->hasMessages()) {
                    $this->messageManager->addError(
                        __("Shipment Document Validation Error(s):\n" . implode("\n", $validationResult->getMessages()))
                    );
                    $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                    return;
                }
                $shipment->register();

                $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $responseAjax = new \Magento\Framework\DataObject();

                if ($isNeedCreateLabel) {
                    $this->labelGenerator->create($shipment, $this->_request);
                    $responseAjax->setOk(true);
                }

                $this->_saveShipment($shipment);
                $order->setStatus('complete');
                $order->setState('complete');
                $order->save();
                if (!empty($data['send_email'])) {
                    $this->shipmentSender->send($shipment);
                }

                $shipmentCreatedMessage = __('The shipment has been created.');
                $labelCreatedMessage = __('You created the shipping label.');

                $this->messageManager->addSuccess(
                    $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
                );
                $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($isNeedCreateLabel) {
                    $responseAjax->setError(true);
                    $responseAjax->setMessage($e->getMessage());
                } else {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                }
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                if ($isNeedCreateLabel) {
                    $responseAjax->setError(true);
                    $responseAjax->setMessage(__('An error occurred while creating shipping label.'));
                } else {
                    $this->messageManager->addError(__('Cannot save shipment.'));
                    $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                }
            }
            if ($isNeedCreateLabel) {
                $this->getResponse()->representJson($responseAjax->toJson());
            } else {
                $this->_redirect('sales/order/view', ['order_id' => $shipment->getOrderId()]);
            }
        }
    }


    /**
     * @return \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface
     * @deprecated
     */
    private function getShipmentValidator()
    {
        if ($this->shipmentValidator === null) {
            $this->shipmentValidator = $this->_objectManager->get(
                \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface::class
            );
        }

        return $this->shipmentValidator;
    }
}

