<?php
// php /var/www/html/as_moma/bin/magento ps:last3daynotice

namespace Astralweb\ShippingStorePickUp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Store\Model\ScopeInterface;
class last3dayNotice extends Command
{
    /**
     * Injected Dependency Description
     *
     * @var \Astralweb\ShippingStorePickUp\Helper\Data
     */
    protected $ShippingStorePickUp_helperData;


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
    protected $Sms_helperData;

    /**
     * Injected Dependency Description
     *
     * @var \\Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesModelResourceModelOrderCollectionFactory;

    /**
     * Injected Dependency Description
     *
     * @var \\Magento\Sales\Model\Order
     */
    protected $salesModelOrder;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $frameworkAppConfigScopeConfigInterface,
        \Magento\Framework\App\State $state,
        \Astralweb\Sms\Helper\Data $Sms_helperData,
        \Magento\Sales\Api\OrderRepositoryInterface $apiOrderRepositoryInterface,
        \Magento\Sales\Model\Order $salesModelOrder,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesModelResourceModelOrderCollectionFactory,
        \Astralweb\ShippingStorePickUp\Helper\Data $ShippingStorePickUp_helperData)
    {
        $this->Sms_helperData = $Sms_helperData;
        $this->_transportBuilder = $transportBuilder;
        $this->frameworkAppConfigScopeConfigInterface = $frameworkAppConfigScopeConfigInterface;
        $state->setAreaCode('frontend');
        $this->ShippingStorePickUp_helperData = $ShippingStorePickUp_helperData;
        $this->salesModelResourceModelOrderCollectionFactory = $salesModelResourceModelOrderCollectionFactory;
        $this->apiOrderRepositoryInterface = $apiOrderRepositoryInterface;
        $this->salesModelOrder = $salesModelOrder;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName("ps:last3daynotice");
        $this->setDescription("last 3 day notice customer by mail and sms");
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/last3daynotice.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('==============================');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->get('Magento\Variable\Model\Variable')->loadByCode('DEBUG_MODEL');
        $DEBUG_MODEL = $model->getName();

        $arrival_time = mktime(0,0,0,date('m'), date('d')-7, date('Y'));   // 取貨期限前三天
        $arrival_date = date('Y-m-d',$arrival_time);

        $start = $arrival_date.' 00:00:00';
        $end   = $arrival_date.' 23:59:59';

        $salesOrderCollection = $this->salesModelResourceModelOrderCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('arrival_datetime',  ['gteq' => $start])
            ->addFieldToFilter('arrival_datetime',  ['lteq' => $end ])
            //->addFieldToFilter('shipping_method' , ['like' => 'ShippingStorePickUp_%' ])
            //->addFieldToFilter('shipping_method' , ['eq' => 'ShippingStorePickUp_ShippingStor' ])
            ->addFieldToFilter('get_code' ,         ['notnull' => true ])
            ->addFieldToFilter('get_datetime' ,     ['null' => true ])
            ->load();

        $allIds = $salesOrderCollection->getAllIds();

// exit;
        if( $DEBUG_MODEL ){
            $allIds = array("111244740");
            $output->writeln( json_encode($allIds) );
        }

        foreach( $allIds as $id ){
            $order = $this->apiOrderRepositoryInterface->get($id);

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
                ->setTemplateIdentifier('store_pick_up_last_3_days_notice')      // template id 若是後台template 就看編號  last3daynotice
                ->setTemplateOptions([
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,              // frontend
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID             // 0
                ])
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo($billingAddress->getEmail())                                    // 收件人
                ->getTransport();
            $transport->sendMessage();
            if( ! $DEBUG_MODEL ){
                // 發送簡訊

                $phoneBilling = $billingAddress->getData('telephone');

                $shippingAddress = $order->getShippingAddress();

                // $shop = $shippingAddress->getCity() .$shippingAddress->getRegion().$shippingAddress->getStreet()[0];
                $shop = $shippingAddress->getStreet()[0];

                $this->Sms_helperData->setTo('+886'.substr($phoneBilling,1));
                //$this->Sms_helperData->setText('親愛的MOMA會員您好：提醒您，您所訂購的商品已送達'.$shop.'，取貨代碼為：'.$Get_code.'，請於七天內攜帶有證件照片完成取貨。');
                $this->Sms_helperData->setText('親愛的MOMA會員您好：提醒您，您所訂購的商品已送達'.$shop.'，取貨期限剩餘3天，您的取貨代碼為：'.$Get_code.'，請盡快前往取貨，若已取貨請忽略此簡訊。');
                $this->Sms_helperData->send();
            }
        }
    }
}