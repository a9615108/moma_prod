<?php
namespace Astralweb\ShippingStorePickUp\Model;
use Astralweb\ShippingStorePickUp\Api\UpdateOrderCarryInterface;
use Magento\Store\Model\ScopeInterface;
class UpdateOrderCarry implements UpdateOrderCarryInterface {
    /**
     * Injected Dependency Description
     *
     * @var \\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $frameworkAppConfigScopeConfigInterface;

    /**
     * Injected Dependency Description
     *
     * @var \Astralweb\Sms\Helper\Data
     */
    protected $helperData;

    /**
     * Injected Dependency Description
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $modelOrder;

    public function __construct(
        \Astralweb\ShippingStorePickUp\Helper\Data $ShippingStorePickUp_helperData,
        \Magento\Sales\Api\Data\OrderInterface $modelOrder,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Astralweb\Sms\Helper\Data $helperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $frameworkAppConfigScopeConfigInterface)
    {
        $this->ShippingStorePickUp_helperData = $ShippingStorePickUp_helperData;
        $this->frameworkAppConfigScopeConfigInterface = $frameworkAppConfigScopeConfigInterface;
        $this->helperData = $helperData;
        $this->modelOrder = $modelOrder;
        $this->_transportBuilder = $transportBuilder;
    }

    /*
        訂單狀態
            抵達門市    arrival_shop
            已完成取件  completed_pickup
            未完成取件  unfinished_pickup
    */

    public $rearr = array();

    public function index() {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info('==============================');
        $logger->info(json_encode( $_POST ));

        $Order_id     = $this->fetch_POST('Order_id');
        $Carry_mode   = $this->fetch_POST('Carry_mode');   // 回傳1 or 2 ，1=到貨 / 2=取貨
        $Arrival_rec  = $this->fetch_POST('Arrival_rec');  // Y=商品已到店
        $Arrival_date = $this->fetch_POST('Arrival_date'); // 到店日期，格式YYYYMMDD
        $Arrival_time = $this->fetch_POST('Arrival_time'); // 到店時間，格式HH:MM:SS
        $Get_code     = $this->fetch_POST('Get_code');     // 消費者取貨用，長度20碼
        $Get_rec      = $this->fetch_POST('Get_rec');      // Y=消費者已取貨，N=消費者未取貨
        $Get_date     = $this->fetch_POST('Get_date');     // 取貨日期，格式YYYYMMDD
        $Get_time     = $this->fetch_POST('Get_time');     // 取貨時間，格式HH:MM:SS

        $this->rearr = array(
            'Order_id'          => $Order_id,
            'Carry_mode'        => $Carry_mode,
            'responseMessage'   => 'N'
        );

        if( empty($Order_id) ){
            $this->set_rearr_date();
            return $this->rearr;
        }

        $order = $this->modelOrder->load($Order_id);

        switch( $Carry_mode ){
            case '1':   // 到貨
                if( $Arrival_rec != 'Y' ){
                    $this->set_rearr_date();
                    return $this->rearr;
                }

                $datetime = $this->datetime_trans_func($Arrival_date, $Arrival_time);

                $order->setState('arrival_shop');       // 更新訂單狀態 : 抵達門市
                // $order->setStatus('arrival_shop');

                $order->setArrivalDatetime($datetime);  // 紀錄 到店時間日期
                $order->setGetCode($Get_code);          //      取貨編號
                $order->save();

                $billingAddress = $order->getBillingAddress();
                $shippingAddress = $order->getShippingAddress();

                // 發送 emila
                $emailTempVariables = array(            // 要帶入 template 的變數
                    'order' => $order,
                    'shipping' => $order->getShippingAddress(),
                    'deadline' => $this->ShippingStorePickUp_helperData->get_get_date($arrival_date) ,
                    'Street' => $shippingAddress->getStreet()[0] ,
                );
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($emailTempVariables);

                $sender = [                                                                 // 寄件人資訊
                    'name'  => $this->frameworkAppConfigScopeConfigInterface->getValue('trans_email/ident_general/name' ,ScopeInterface::SCOPE_STORE),
                    'email' => $this->frameworkAppConfigScopeConfigInterface->getValue('trans_email/ident_general/email',ScopeInterface::SCOPE_STORE),
                ];

                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier('store_pick_up_arrival_shop')      // template id 若是後台template 就看編號
                    ->setTemplateOptions([
                        'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,              // frontend
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID             // 0
                    ])
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender)
                    ->addTo($billingAddress->getEmail())                                    // 收件人
                    ->getTransport();
                $transport->sendMessage();

                // 發送到貨簡訊

                $phoneBilling = $billingAddress->getData('telephone');

                $shippingAddress = $order->getShippingAddress();

                // $shop = $shippingAddress->getCity() .$shippingAddress->getRegion().$shippingAddress->getStreet()[0];
                $shop = $shippingAddress->getStreet()[0];

                $this->helperData->setTo('+886'.substr($phoneBilling,1));
                $this->helperData->setText('親愛的MOMA會員您好：提醒您，您所訂購的商品已送達{'.$shop.'}，取貨代碼為：'.$Get_code.'，請於七天內攜帶有證件照片完成取貨。');
                $this->helperData->send();

                $this->rearr['responseMessage'] = 'Y';

                break;
            case '2':   // 取貨

                if( $Get_rec == 'Y' ){

                    $order->setState('completed_pickup');       // 更新訂單狀態 : 已完成取件
                    //$order->setStatus('completed_pickup');

                    $datetime = $this->datetime_trans_func($Get_date, $Get_time);
                    $order->setArrivalDatetime($datetime);      // 紀錄 取貨時間日期
                }
                else{
                    $order->setState('unfinished_pickup');      // 更新訂單狀態 : 未完成取件
                    //$order->setStatus('unfinished_pickup');
                }

                $this->rearr['responseMessage'] = 'Y';

                break;
        }

        $this->set_rearr_date();
        return $this->rearr;

    }

    function fetch_POST($key=''){
        return isset($_POST[$key])?$_POST[$key]:'';
    }

    function set_rearr_date(){
        $this->rearr['responseDate'] = date('Ymd');
        $this->rearr['responseTime'] = date('H:i:s');
    }

    /*
        input  "20160127" , "17:27:12"
        output '2016/01/27 17:27:12'
    */
    function datetime_trans_func($Date, $Time){

        $datetime = substr($Date,0,4) . '/' .
            substr($Date,4,2) . '/' .
            substr($Date,6,2) . ' ' .
            $Time;

        return $datetime ;
    }
}