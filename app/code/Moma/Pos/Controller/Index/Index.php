<?php

namespace Moma\Pos\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\ScopeInterface;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_orderCollectionFactory;
    protected $_orderPosCollectionFactory;
    protected $_jsonHelper;
    protected $scopeConfig;
    protected $storeManager;
    protected $directory_list;
    protected $_helper;
    protected $_posFactory;
    protected $_localeDate;

    const XML_PATH_POS = 'moma_pos_section/general/enable';
    const XML_PATH_LOGGING = 'moma_pos_section/general/logging';

    public function __construct(Context $context,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
                                \Moma\Pos\Model\ResourceModel\Pos\CollectionFactory $orderPosCollectionFactory,
                                \Magento\Framework\Json\Helper\Data $jsonHelper,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
                                \Moma\Pos\Helper\Data $helper,
                                \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
                                \Moma\Pos\Model\PosFactory $posFactory)
    {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderPosCollectionFactory = $orderPosCollectionFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->directory_list = $directory_list;
        $this->_helper = $helper;
        $this->_localeDate = $localeDate;
        $this->_posFactory = $posFactory;
        parent::__construct($context);
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_POS, ScopeInterface::SCOPE_STORE)) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
//        foreach ($params as $key => $value) {
//            $data = $key;
//        }
//        $data = json_decode($data);
        $user = $this->_helper->getPosUser();
        $token = $this->_helper->getPosToken();
        $method = $this->_helper->getPosMethod();
        if ($params['user'] == $user && $params['token'] == $token && $params['method'] == $method) {

            $fromDate = date_format(date_create($params['from_date']), 'm/d/Y');
            $toDate = date_format(date_create($params['to_date']), 'm/d/Y');

            $locale = $this->storeManager->getStore()->getLocaleCode();
            $dateFrom = date('Y-m-d 00:00:00', strtotime($fromDate));
            $dateTo = date('Y-m-d 23:59:59', strtotime($toDate));

            $date_from = new \Zend_Date(strtotime($dateFrom), \Zend_Date::TIMESTAMP, $locale);
            $date_to = new \Zend_Date(strtotime($dateTo), \Zend_Date::TIMESTAMP, $locale);


            $date_from->sub(8, \Zend_Date::HOUR);
            $date_to->sub(8, \Zend_Date::HOUR);

            $dateFr = $date_from->get('YYYY-MM-dd HH:mm:ss');
            $dateT = $date_to->get('YYYY-MM-dd HH:mm:ss');

            $collection = $this->_getCollectionOrder();
            $collection->addFieldToSelect('*')
                ->addFieldToFilter('created_at', array('from'=>$dateFr, 'to'=>$dateT));
            $order_pos_arr = $this->_getPosOrderArray();
            $result['result'] = array();
            $orderCount = 0;
            foreach ($collection as $order) {
                if (in_array($order->getIncrementId(), $order_pos_arr)) {
                    $invoice_type = "";
                    if ($order->getData('invoice_type') == 'two') {
                        $invoice_type = 1;
                    } elseif ($order->getData('invoice_type') == 'three') {
                        $invoice_type = 2;
                    }
                    if ($order->getData('shipping_method')=='collect_storecvs_collect_storecv'){
                        $shippingMethod='超商';
                        $storeLocal=NULL;
                    }elseif ($order->getData('shipping_method')=='collect_store_collect_store'){
                        $shippingMethod='宅配';
                        $storeLocal=NULL;
                    }elseif ($order->getData('shipping_method')=='ShippingStorePickUp_ShippingStor'){
                        $shippingMethod='門市';
                        $storeLocal=$order->getData('shop_id');
                    }
                    $customerId = $order->getCustomerId();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $customerRepository = $objectManager
                        ->get('Magento\Customer\Api\CustomerRepositoryInterface');
                    $customer = $customerRepository->getById($customerId);
                    $vipNumObj = $customer->getCustomAttribute('vip_num');
                    $vipNum = '';
                    if($vipNumObj) $vipNum =  $vipNumObj->getValue();

                    $order_date = (array) $this->_localeDate->date(new \DateTime($order->getData('created_at')));
                    $result['result'][$orderCount]['order_id'] = $order->getIncrementId();
                    $result['result'][$orderCount]['order_date'] = date('Y-m-d H:i:s', strtotime($order_date['date']));
                    $result['result'][$orderCount]['status'] = $order->getData('status');
                    $result['result'][$orderCount]['shipping_method'] = $shippingMethod;
                    $result['result'][$orderCount]['store_location'] = $storeLocal;
                    $result['result'][$orderCount]['qty'] = $order->getData('total_qty_ordered') * 1;
                    $result['result'][$orderCount]['grand_total'] = $order->getData('grand_total') * 1;
                    $result['result'][$orderCount]['subtotal'] = $order->getData('subtotal') * 1;
                    $result['result'][$orderCount]['shipping_amount'] = $order->getData('shipping_amount') * 1;
                    $result['result'][$orderCount]['payment_method'] = $order->getData('payment_method');
                    $result['result'][$orderCount]['first_6_digit_of_pan'] = $order->getData('first_6_digit_of_pan');
                    $result['result'][$orderCount]['last_4_digit_of_pan'] = $order->getData('last_4_digit_of_pan');
                    $result['result'][$orderCount]['invoice_type'] = $invoice_type;
                    if($order->getData('purchaser_name') != null){
                        $dataDonation = explode(',',$order->getData('purchaser_name'));
                        $result['result'][$orderCount]['donations_code'] = $dataDonation[0];
                    }else{
                        $result['result'][$orderCount]['donations_code'] = "";
                    }
                    // $result['result'][$orderCount]['donations_code'] = $order->getData('purchaser_name') != null ? $order->getData('purchaser_name') : "";
                    $result['result'][$orderCount]['vat_no'] = $order->getData('tax_id') != null ? $order->getData('tax_id') : "";
                    $result['result'][$orderCount]['carrier_code_a'] = "";
                    $result['result'][$orderCount]['carrier_type'] = "";
                    $result['result'][$orderCount]['carrier_code_b'] = "";
                    $result['result'][$orderCount]['type'] = 1;
                    $result['result'][$orderCount]['invoice_no'] = "";
                    $result['result'][$orderCount]['phone'] = $order->getBillingAddress()->getTelephone();
                    $result['result'][$orderCount]['vip_no'] = $vipNum;
                    $result['result'][$orderCount]['customer_name'] = $order->getCustomerName();
//                    $result['result'][$orderCount]['store_location'] = "";
                    $productCount = 0;
                    $product_id_count = 1;
                    foreach ($order->getAllVisibleItems() as $item_order) {
                        $result['result'][$orderCount]['products'][$productCount]['id'] = $product_id_count;
                        $result['result'][$orderCount]['products'][$productCount]['qty'] = $item_order->getData('qty_ordered') * 1;
                        $result['result'][$orderCount]['products'][$productCount]['sku'] = $item_order->getData('sku');
                        $result['result'][$orderCount]['products'][$productCount]['price'] = $item_order->getData('price') * 1;
                        $result['result'][$orderCount]['products'][$productCount]['subtotal'] = $item_order->getData('price') * $item_order->getData('qty_ordered');
                        $result['result'][$orderCount]['products'][$productCount]['total'] = $item_order->getData('price') * $item_order->getData('qty_ordered');
                        $productCount++;
                        $product_id_count++;
                    }
//                    $pos_model = $this->_posFactory->create()->load($order->getIncrementId(), 'order_id');
                    // if ($pos_model->getData()) {
                    //     $pos_model->setData('sync', 1);
                    //     $pos_model->save();
                    // }
                    $orderCount++;
                }


            }
            $result['responseTime'] = date('Y-m-d H:i:s');
            $data_response = $this->_jsonHelper->jsonEncode($result);
            if ($this->scopeConfig->isSetFlag(self::XML_PATH_LOGGING, ScopeInterface::SCOPE_STORE)) {
//                file_put_contents($this->directory_list->getPath('var') . '/log/pos.log', $params, FILE_APPEND);
                $folderExist = is_dir($this->directory_list->getPath('var') . '/log/pos/');
                if (!$folderExist) {
                    mkdir($this->directory_list->getPath('var') . '/log/pos/', 0755, true);
                }
                file_put_contents($this->directory_list->getPath('var') . '/log/pos/'. date('Y-m-d H:i:s') .'.log', $data_response, FILE_APPEND);
            }

            return $this->_response->setHttpResponseCode(200)
                ->setHeader('Content-type', 'application/json', true)
                ->setBody($data_response)
                ->sendResponse();
        }
        return 'error!!!';
    }

    protected function _getCollectionOrder() {
        $orderPaymentCODCollection = $this->_orderCollectionFactory->create()
            ->join(
                array('payment' => 'sales_order_payment'),
                'main_table.entity_id=payment.parent_id',
                array('payment_method' => 'payment.method')
            )
            ->join(
                array('invoicetype' => 'astralweb_invoicetype'),
                'main_table.entity_id=invoicetype.order_id',
                array('tax_id' => 'tax_id', 'purchaser_name' => 'purchaser_name', 'invoice_type' => 'invoice_type')
            )
        ;
        return $orderPaymentCODCollection;
    }

    protected function _getPosOrderArray() {
        $pos_orders = $this->_orderPosCollectionFactory->create();
        $pos_orders->addFieldToSelect('*');
        $order_pos_arr = array();
        foreach ($pos_orders as $pos_order) {
            $order_pos_arr[] = $pos_order->getData('order_id');
        }
        return $order_pos_arr;
    }
}
