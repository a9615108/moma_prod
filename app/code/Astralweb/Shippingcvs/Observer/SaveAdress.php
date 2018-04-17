<?php
namespace Astralweb\Shippingcvs\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;



class SaveAdress implements ObserverInterface{
    /** @var \Magento\Framework\Logger\Monolog */
    const COOKIE_NAME = 'shippingcvs';
    const COOKIE_DURATION = 6;

    protected $logger;
    protected $_helper;
    protected $templateContainer;
    protected $identityContainer;
    protected $senderBuilderFactory;
    protected $sender;
    protected $inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    public function __construct(\Psr\Log\LoggerInterface $loggerInterface,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {

        $this->logger = $loggerInterface;
        $this->_scopeConfig = $scopeConfig;


    }
    /**
     * fires when sales_order_save_after is dispatched
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {

        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getEntityId();
        $IncrementId = $order->getIncrementId();
        $quoteId = $order->getQuoteId();
        $shippingAddress = $order->getShippingAddress();
        $shippingMethod = $order->getShippingMethod();
        if($shippingMethod == 'collect_storecvs_collect_storecvs'){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
            $cookieMetadataFactory = $objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');
            $sessionManager = $objectManager->get('Magento\Framework\Session\SessionManagerInterface');
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $dataCookie=$cookieManager->getCookie('shippingcvs');
            if(isset($dataCookie) && $dataCookie){
                $data = explode(',', $dataCookie);
                $customerAdressId = $shippingAddress->getData('customer_address_id');
                $tableName = $resource->getTableName('astralweb_shippingcvs');
                $tableQuoteAdress = $resource->getTableName('quote_address');
                $sqlGetQuoteAdress = "SELECT * FROM " . $tableQuoteAdress ." WHERE quote_id =".$quoteId." AND address_type = '"."shipping'";
                $resultQuoteAdress = $connection->fetchAll($sqlGetQuoteAdress);
                if($resultQuoteAdress[0]['save_in_address_book'] == 0){

                    $tableCustomerEntity = $resource->getTableName('customer_address_entity');
                    if(isset($customerAdressId) && $customerAdressId){
                            $sqlInsert = "INSERT INTO " . $tableName . " (increment_id, cvsspot,cvsnum,status,bc1,bc2) VALUES ('".$IncrementId."','".$data[3]."','".$data[4]."',0,NULL,NULL)";
                            $connection->query( $sqlInsert);
                    }else{

                        $sqlInsert = "INSERT INTO " . $tableName . " (increment_id, cvsspot,cvsnum,status,bc1,bc2) VALUES ('".$IncrementId."','".$data[3]."','".$data[4]."',0,NULL,NULL)";
                        $connection->query($sqlInsert);
                    }

                }else{
                    $tableCustomerEntity = $resource->getTableName('customer_address_entity');
                    $sqlDeleteCustomerAdress = "DELETE FROM " . $tableCustomerEntity." WHERE entity_id = ".$customerAdressId;
                    $connection->query($sqlDeleteCustomerAdress);
                    $sqlInsert = "INSERT INTO " . $tableName . " (increment_id, cvsspot,cvsnum,status,bc1,bc2) VALUES ('".$IncrementId."','".$data[3]."','".$data[4]."',0,NULL,NULL)";
                    $connection->query($sqlInsert);
                }
                $cookieManager->deleteCookie(\Astralweb\Shippingcvs\Controller\Index\Index::COOKIE_NAME);

                $metadata = $cookieMetadataFactory->createPublicCookieMetadata()->setDuration(self::COOKIE_DURATION);
                $cookieManager->setPublicCookie(self::COOKIE_NAME,NULL,$metadata);
                setcookie(self::COOKIE_NAME,NULL,time()-3600,"/"); 
            }
        }
    }




}



