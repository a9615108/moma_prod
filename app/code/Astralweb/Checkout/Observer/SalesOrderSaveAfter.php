<?php

namespace Astralweb\Checkout\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
// use Vendor\Customer\Model\Customer;
// use Vendor;

class SalesOrderSaveAfter implements ObserverInterface {
    /** @var \Magento\Framework\Logger\Monolog */

    protected $Product_model;
    protected $Category_model;
    protected $Iguang;
    protected $logger;
    protected $_order;

    public function __construct(
        \Magento\Catalog\Model\Product $Product_model,
        \Magento\Catalog\Model\Category $Category_model,
        \Astralweb\Iguang\Helper\Data $Iguang,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Psr\Log\LoggerInterface $loggerInterface
    ) 
    {
        $this->Product_model = $Product_model;
        $this->Category_model = $Category_model;
        $this->Iguang  = $Iguang;
        $this->logger = $loggerInterface;
        $this->_order = $order;
    }

    /**
     * fires when sales_order_save_after is dispatched
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer) {

        if( empty($_COOKIE["ecid"]) ){
            return;
        }

        $ecid       = $_COOKIE["ecid"];
        $utm_source = isset($_COOKIE["utm_source"])?$_COOKIE["utm_source"]:'';
        $utm_medium = isset($_COOKIE["utm_medium"])?$_COOKIE["utm_medium"]:'';

$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/orderinfo.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$logger->info(' ============================== ');

        $orderinfo = array();

        $orderids = $observer->getEvent()->getOrderIds();

        foreach($orderids as $orderid){
            $order = $this->_order->load($orderid);
        }
        // $orderid = $order->getIncrementId();
        $orderid = $order->getIncrementId();                // 訂單編號 (改成一般用戶看得到的編號)

        $orderinfo['site']    = $this->Iguang::SITE;        // 導購媒體代號
        $orderinfo['shopid']  = $this->Iguang::SHOPID;      // 商城代號
        $orderinfo['authkey'] = $this->Iguang::AUTHKEY;     // 認證登入代碼
        $orderinfo['orderid'] = $orderid;                   // 訂單編號
        $orderinfo['ordertotal'] = (int)$order->getSubtotal(); // 商品訂單交易金額 (不含運費)  // base_subtotal subtotal
        $orderinfo['ordertime'] = $order->getCreatedAt();   // 訂單成立時間

        $orderItems = $order->getAllItems();

        $orderItems_data = $this->Iguang->get_orderItems_data($orderItems, $this->Product_model, $this->Category_model );

        $order_list = array();
        foreach( $orderItems_data['son'] as $item ){

            $product = array(
                'product_name'  => $orderItems_data['parent'][$item['parent']]['product_name'],
                'product_type'  => $item['product_type'],
                'product_amount'=> $orderItems_data['parent'][$item['parent']]['product_amount'],
                'sub_category1' => $orderItems_data['parent'][$item['parent']]['sub_category1'],
            );
               
            if( isset( $item['sub_category2'] ) ){
                $product['sub_category2'] = $orderItems_data['parent'][$item['parent']]['sub_category2'];
            }

            $order_list[] = array(
                "product" => $product
            );
        }

        $orderinfo['order_list'] = $order_list;         // 結帳明細

        $orderinfo['ecid'] = $ecid ;  // 用戶識別碼

        $timestamp = time();
        $orderinfo['timestamp']   = $timestamp;        // 時間

        //$date = date('Y-m-d H:i:s',$timestamp);
        $date = $orderinfo['ordertime'];

$logger->info('date : '.$date );

        $ordertime_hashkey = md5($date);

$logger->info('ordertime_hashkey : '.$ordertime_hashkey );
$logger->info('orderid='.$orderid.'&ordertotal='.$orderinfo['ordertotal'].'&timestamp='.$timestamp );

        $hash = hash_hmac('sha256','orderid='.$orderid.'&ordertotal='.$orderinfo['ordertotal'].'&timestamp='.$timestamp,$ordertime_hashkey);

$logger->info('hash : '.$hash );


        $orderinfo['hash']   = $hash;                  // 驗證碼


        $order->setEcid($ecid);
        $order->setUtmSource($utm_source);
        $order->setUtmMedium($utm_medium);
$order->save();
$logger->info('data : '.json_encode($orderinfo));

        $url = $this->Iguang::ORDERINFO_URL;

        $data = http_build_query($orderinfo);

$logger->info('data : '.$data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_ENCODING , "gzip");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
 

        $contents = curl_exec($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

$logger->info('contents : '. $contents);

    }
}