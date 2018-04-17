<?php
namespace Moma\Pos\Controller\Invoice;

use Zend_Barcode;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Asset\Repository;

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
    protected $transportBuilder;
    protected $_assetRepo;
    protected $orderFactory ;

    const XML_PATH_POS = 'moma_pos_section/general/enable';
    const XML_PATH_LOGGING = 'moma_pos_section/general/logging';
    const XML_PATH_POS_TOKEN = 'moma_pos_section/general/token';

    public function __construct(Context $context,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
                                \Moma\Pos\Model\ResourceModel\Pos\CollectionFactory $orderPosCollectionFactory,
                                \Magento\Framework\Json\Helper\Data $jsonHelper,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
                                \Moma\Pos\Helper\Data $helper,
                                \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
                                \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
                                \Magento\Framework\View\Asset\Repository $assetRepo,
                                \Magento\Sales\Model\OrderFactory $orderFactory,
                                \Magento\Framework\Event\Observer $observer,
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
        $this->transportBuilder = $transportBuilder;
        $this->_assetRepo = $assetRepo;
        $this->orderFactory = $orderFactory;

        parent::__construct($context);
    }

    private function make_barcode($invoice=''){
        $barcodeOptions = array('text' => $invoice );
        $rendererOptions = array();
        $imageResource = Zend_Barcode::draw(
            'code39', 'image', $barcodeOptions, $rendererOptions
        );
        imagepng($imageResource, 'email_barcode/'.$invoice.'.png');

        $data = file_get_contents('email_barcode/'.$invoice.'.png');
        $type = pathinfo($invoice, PATHINFO_EXTENSION);
        $invoice_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $invoice_src = $invoice . '.png';
        //unset($invoice_src);
        //return $invoice_base64;
        return $this->storeManager->getStore()->getBaseUrl() . 'email_barcode/'.$invoice . '.png';
    }

    public function execute(){

        $params = $this->getRequest()->getParams();

        // 寫 log
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Moma_Pos_Invoice.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($params);

        /* // error message
            20  => 'Success'        // 只有這個有規定  其他都自訂的
            404 => 'no data'
            444 => 'token error'
            500 => 'update false'
            405 => 'error entity_id'
        */

        $return = array();
        $return['result'] = "Order updated";
        $return['responseDate'] = date('Ymd');
        $return['responseTime'] = date('H:i:m');

        $method         = isset($params['method'])  ? $params['method']  :'';
        $user           = isset($params['user'])    ? $params['user']    :'';
        $token          = isset($params['token'])   ? $params['token']   :'';
        $increment_id   = isset($params['order_id'])? $params['order_id']:'';   // increment_id
        $invoice        = isset($params['invoice']) ? $params['invoice'] :'';

        if( $increment_id == '' || $invoice == '' ){
            $return['responseCode'] = 404;
            $return['responseMessage'] = 'no data';
            return $this->return_cont($return);
        }

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $moma_pos_section_general_token =  $this->scopeConfig->getValue(self::XML_PATH_POS_TOKEN, $storeScope);

        if( $token != $moma_pos_section_general_token ){
            $return['responseCode'] = 444;
            $return['responseMessage'] = 'token error';
            return $this->return_cont($return);
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('astralweb_invoicetype');

        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($increment_id);
        $entity_id = $order->getEntity_id();

        if( empty($entity_id) ){
            $return['responseCode'] = 405;
            $return['responseMessage'] = 'error entity_id';
            return $this->return_cont($return);
        }

        $sql = "update " . $tableName."
                SET order_invoice = '". $invoice ."'
                WHERE order_id = '".$entity_id."'
                LIMIT 1";
        $result = $connection->query($sql);

        if( $result ){
            $return['responseCode'] = 20;
            $return['responseMessage'] = 'Success';
        }else{
            $return['responseCode'] = 500;
            $return['responseMessage'] = 'update false';
        }

        // 取得 信用卡 後四碼
        $tableName_sales_order = $resource->getTableName('sales_order');
        $sql = "select last_4_digit_of_pan from " . $tableName_sales_order."
                WHERE entity_id = '".$entity_id."'
                LIMIT 1";
        $last_4_digit_of_pan = $connection->fetchOne($sql);

        // 訂單資訊
        $letter_data = array(
            'uninum'            => '',                          // 統一編號
            'invoice'           => $invoice,                    // 發票號碼
            'CustomerName'      => $order->getCustomerName(),   // 買受人
            'address'           => '',                          // 地址
            'increment_id'      => $increment_id,               // 訂單編號
            'incrementId'       => [],                          // 明細編號
            'total'             => $order->getGrandTotal(),     // 總價
            'order_item'        => [],                          // 商品明細

            //'invoice_base64'        => $this->make_barcode($invoice),       // 發票號碼 base64
            //'increment_id_base64'   => $this->make_barcode($increment_id),  // 訂單編號 base64
            'incrementId_base64'    => '',                                  // 明細編號 base64

            'stamp_png_url'     => $this->_assetRepo->getUrl("images/pos/stamp.png"),
            'MOMA_08_png_url'   => $this->_assetRepo->getUrl("images/pos/MOMA_08.png"),
            'fafa_b_png_url'    => $this->_assetRepo->getUrl("images/pos/fafa_b.png"),
            'fafa2_png_url'     => $this->_assetRepo->getUrl("images/pos/fafa2.png"),

            'year'              => (date('Y')-1911),
            'month'             => date('m'),
            'day'               => date('d'),

            'last_4_digit_of_pan'   => $last_4_digit_of_pan,
        );

        // 統一編號
        $sql = "select tax_id from " . $tableName."
                WHERE order_id = '".$entity_id."'
                and tax_id is not NULL
                and invoice_type  = 'two'
                LIMIT 1";
        $letter_data['uninum'] = $connection->fetchOne($sql);

        // 帳單地址
        $billingAddress = $order->getBillingAddress();

        // 配送地址
        // $shippingAddress = $order->getShippingAddress();
        $address =  $billingAddress->getPostcode()  . ', '.
            $billingAddress->getCity()      . ', '.
            $billingAddress->getRegion()    . ', '.
            join(', ',$billingAddress->getStreet());
        $letter_data['address'] = $address;

        // 明細編號
        $incrementId = [];
        foreach ($order->getInvoiceCollection() as $invoiceCollection){
            $incrementId[] = $invoiceCollection->getIncrementId();
        }
        $letter_data['incrementId'] = join(', ',$incrementId);

        //$letter_data['incrementId_base64'] = $this->make_barcode($letter_data['incrementId']);

        // 訂單明細
        $order_item = array();
        foreach($order->getAllItems() as $item){
            if( $itemQty = $item->getQtyToShip() ){
                $proName      = $item->getName();
                $itemPrice    = $item->getPrice();
                $RowTotal     = $item->getRowTotal();
                $itemSku      = $item->getSku();

                $order_item[$itemSku] = array(
                    'proName'    => $proName,
                    'itemPrice'  => $itemPrice,
                    'RowTotal'   => $RowTotal,
                    'itemQty'    => $itemQty,
                    'color'      => '',
                    'size'       => '',
                );

                $options = $item->getProductOptions();
                if (isset($options['attributes_info']) ) {
                    foreach ($options['attributes_info'] as $option) {
                        switch($option['label']){
                            case '顏色':
                                $order_item[$itemSku]['color'] = $option['value'];
                                break;
                            case '尺碼':
                                $order_item[$itemSku]['size'] = $option['value'];
                                break;
                        }
                    }
                }
            }
        }
        // $letter_data['order_item'] = $order_item;
        $order_item_html = '';
        foreach($order_item as $itemSku => $item){
            $order_item_html .= '<ul style="background: #f2f2f2; color: #4C4C4C;padding: 5px 0;">';
            $order_item_html .=     '<li style="width: 25%;text-indent: 20px;">'.$item['proName'].'</li>';
            $order_item_html .=     '<li style="width: 10%;text-align: center;">'.$item['color'].'</li>';
            $order_item_html .=     '<li style="width: 10%;text-align: center;">'.$item['size'].'</li>';
            $order_item_html .=     '<li style="width: 20%;text-align: center;">'.$itemSku.'</li>';
            $order_item_html .=     '<li style="width: 10%;text-align: center;">'.$item['itemQty'].'</li>';
            $order_item_html .=     '<li style="width: 10%;text-align: center;">'.$item['itemPrice'].'</li>';
            $order_item_html .=     '<li style="width: 10%;text-align: center;">'.$item['RowTotal'].'</li>';
            $order_item_html .= '</ul>';
        }

        $letter_data['order_item_html'] = $order_item_html;

        // 收件者
        $email = $order->getCustomerEmail();

        // 寄件人資訊
        $sender = [
            'name' =>  $this->scopeConfig->getValue('trans_email/ident_sales/name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'email' => $this->scopeConfig->getValue('trans_email/ident_sales/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('invoice_notice')
            ->setTemplateOptions([
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,              // frontend
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID             // 0
            ])
            ->setTemplateVars($letter_data)
            ->setFrom($sender)
            ->addTo($email)
            ->getTransport();
        $transport->sendMessage();

        return $this->return_cont($return);
    }
    public function return_cont($return){
        $return = $this->_jsonHelper->jsonEncode($return);
        return $this->_response->setHttpResponseCode(200)
            ->setHeader('Content-type', 'application/json', true)
            ->setBody($return)
            ->sendResponse();
    }
}