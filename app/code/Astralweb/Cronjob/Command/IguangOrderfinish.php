<?php
/*
    php /var/www/html/as_moma/bin/magento ps:IguangOrderfinish

    每日商品增刪修檔案(更新商品檔案)
*/
namespace Astralweb\Cronjob\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class IguangOrderfinish extends Command
{

    protected $salesOrderCollectionFactory;
    protected $Product_model;
    protected $Category_model;
    protected $Iguang;

    protected function configure()
    {
        $this->setName("ps:IguangOrderfinish");
        $this->setDescription("A command the programmer was too lazy to enter a description for.");
        parent::configure();
    }

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\Product $Product_model,
        \Magento\Catalog\Model\Category $Category_model,
        \Astralweb\Iguang\Helper\Data $Iguang,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\App\State $state
    )
    {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->Product_model = $Product_model;
        $this->Category_model = $Category_model;
        $this->Iguang = $Iguang;
        $this->state = $state;
        $this->order = $order;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->get('Magento\Variable\Model\Variable')->loadByCode('DEBUG_MODEL');
        $DEBUG_MODEL = $model->getName(); 
        if( $DEBUG_MODEL ){
            $this->state->setAreaCode('adminhtml');
        }

        $bef10 =  mktime(0,0,0,date('m'), date('d')-10, date('Y'));;
        $Ymd = date('Y-m-d');

        $start =  date('Y-m-d',$bef10).' 00:00:00';
        $end   =  date('Y-m-d',$bef10).' 23:59:59';

        $salesOrderCollection = $this->orderCollectionFactory->create()->addAttributeToSelect('*')
            ->addFieldToFilter('updated_at', ['gteq' =>  $start])
            ->addFieldToFilter('updated_at', ['lteq' => $end ])
            ->load();

$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/orderfinish.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$logger->info('==============================');

        $orderfinish_all = array();

        $allIds = $salesOrderCollection->getAllIds();
        foreach( $allIds as $orderid ){
        // if( 1 ){    $orderid = '111244740';

            $orderfinish = array();

            $orderfinish['site']    = $this->Iguang::SITE;      // 導購媒體代號
            $orderfinish['shopid']  = $this->Iguang::SHOPID;    // 商城代號
            $orderfinish['authkey'] = $this->Iguang::AUTHKEY;   // 認證登入代碼

            $order = $this->order->load($orderid);

            if( empty($order->getEcid() ) ){
                continue;
            }

            $orderid = $order->getIncrementId();                // 訂單編號 (改成一般用戶看得到的編號)
            $orderfinish['orderid'] = $orderid;                 // 訂單編號

            $orderfinish['ordertotal'] = (int)$order->getTotalInvoiced();    // 商品訂單交易金額
            $orderfinish['ordertime'] = $order->getCreatedAt();         // 訂單成立時間
            
            $orderItems = $order->getAllItems();
     
            $orderItems_data = $this->Iguang->get_orderItems_data($orderItems, $this->Product_model, $this->Category_model );

            $fee_list = array();
            foreach( $orderItems_data['son'] as $item ){

                $product = array(
                    'product_name'  => $orderItems_data['parent'][$item['parent']]['product_name'],
                    'product_type'  => $item['product_type'],
                    'product_fee'   => $orderItems_data['parent'][$item['parent']]['product_amount'],
                    'sub_category1' => $orderItems_data['parent'][$item['parent']]['sub_category1'],
                );
                   
                if( isset( $orderItems_data['parent'][$item['parent']]['sub_category2'] ) ){
                    $product['sub_category2'] = $orderItems_data['parent'][$item['parent']]['sub_category2'];
                }

                $fee_list[] = array(
                    "product" => $product
                );
            }

            $orderfinish['fee_list']    = $fee_list;                    // 結帳明細
            $orderfinish['feetotal']    = (int)$order->getSubtotal();   // 交易付款金額 (不含運費)
            $orderfinish['feetime']     = $order->getCreatedAt();       // 銷售認列時間 (就用訂單成立時間)
            $orderfinish['ecid']        = $order->getEcid();            // 用戶識別碼
            $orderfinish['timestamp']   = time();                       // 時間

            $orderfinish['hash']        = hash_hmac('sha256',  'orderid='   .$orderfinish['orderid'] .
                                                               '&feetime='  .$orderfinish['feetime'] .
                                                               '&feetotal=' .$orderfinish['feetotal'].
                                                               '&timestamp='.$orderfinish['timestamp']
                                                            ,md5($orderfinish['feetime']));;            // 驗證碼

            $url = $this->Iguang::ORDERFINISH_URL;

$logger->info('orderfinish : '.  json_encode( $orderfinish) );
            $data = http_build_query($orderfinish);

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
            curl_close($ch);

$logger->info('contents : '. $contents);

        }
    }
}