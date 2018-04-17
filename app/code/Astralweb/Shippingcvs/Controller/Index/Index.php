<?php

namespace Astralweb\Shippingcvs\Controller\Index;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    const COOKIE_NAME = 'shippingcvs';
    const COOKIE_DURATION = 7200;
    protected $_cookieManager;
    protected $_cookieMetadataFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager)
    {
        //$this->_cookieManager = $cookieManager;
        //        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct($context);
    }
    public function execute()
    {
       
        if(isset($_GET)){
            $cvsid = $_GET['cvsid'];
            $cvsspot = $_GET['cvsspot'];
            $cvstemp = mb_convert_encoding($_GET['cvstemp'], "UTF-8","big5");;
            $cvsname = $_GET['cvsname'];
            $name = mb_convert_encoding($_GET['name'], "UTF-8","big5");;
           // $addr = base64_encode($_GET['addr']);
            $addr = mb_convert_encoding($_GET['addr'], "UTF-8","big5");
            $tel = $_GET['tel'];
            $cvsnum = $_GET['cvsnum'];
        }
       // var_dump($_GET);die;
       

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helperData = $objectManager->get('Astralweb\Shippingcvs\Helper\Data');
        $xmlInfoStore = $helperData->getFileF01();
        $xml = simplexml_load_file($xmlInfoStore);
        $contentsF01 = $xml->xpath('F01CONTENT');
        // var_dump($cvsnum);die;
        foreach ($contentsF01 as $content){
            if($content->STNO == $cvsspot){
                $city = $content->STCITY;
                $zipcode = $content->ZIPCD;
                $address = $content->STADR;
                $telephone = $content->STTEL;
                $namestore = $content->STNM;
            }

        }
        
         // $data = mb_convert_encoding($_GET['addr'], "UTF-8","big5").','.mb_convert_encoding($_GET['tel'], "UTF-8","big5").','.mb_convert_encoding($_GET['name'], "UTF-8","big5").','.$cvsspot.','.$cvsnum.','.$city.','.$zipcode;
        $data = $address.','.$telephone.','.$namestore.','.$cvsspot.','.$cvsnum.','.$city.','.$zipcode;


        //$checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        //$quote = $checkoutSession->getQuote();
        //$quoteId = $quote->getEntityId();

        //Insert data to table shippingcvs
        // if($quoteId){
        //      $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        // $connection = $resource->getConnection();
        // $tableName = $resource->getTableName('astralweb_shippingcvs');
        // $sqlSelect = "SELECT * FROM " . $tableName ." WHERE quote_id =".$quoteId;
        // $result = $connection->fetchAll($sqlSelect); 
        // if(count($result) > 0){
        //     $sqlUpdate = "UPDATE " . $tableName . " SET cvsid = ".$cvsid.", cvsspot = '".$cvsspot."',cvstemp = '".$cvstemp."',cvsname = '".$cvsname."',namecvs = '".$name."',addr = '".$addr."',tel = '".$tel."',cvsnum = '".$cvsnum."' WHERE quote_id = " . $quoteId;
        //      $connection->query($sqlUpdate);     
        // }else{
        //     $sqlInsert = "INSERT INTO " . $tableName . " (quote_id, cvsid, cvsspot, cvstemp, cvsname, namecvs, addr, tel, cvsnum) VALUES ($quoteId,$cvsid,'".$cvsspot."','".$cvstemp."','".$cvsname."','".$name."','".$addr."','".$tel."','".$cvsnum."')";
        //      $connection->query($sqlInsert);     
        // }

            
        // }
       
        //Set cookie
        $cookieMetadataFactory = $objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');
        $cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');

        $cookieValue = $cookieManager->getCookie(\Astralweb\Shippingcvs\Controller\Index\Index::COOKIE_NAME);
        if($cookieValue){
            $cookieManager->deleteCookie(\Astralweb\Shippingcvs\Controller\Index\Index::COOKIE_NAME);
            $metadata = $cookieMetadataFactory->createPublicCookieMetadata()->setDuration(self::COOKIE_DURATION);
            $cookieManager->setPublicCookie(self::COOKIE_NAME,$data,$metadata);
        }else{
            $metadata = $cookieMetadataFactory->createPublicCookieMetadata()->setDuration(self::COOKIE_DURATION);
            $cookieManager->setPublicCookie(self::COOKIE_NAME,$data,$metadata);
        }
        $redirectUrl=$this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl() . 'checkout/';
        // var_dump($redirectUrl);die();
         $jsAction = '
                var standalone = window.navigator.standalone,
            userAgent = window.navigator.userAgent.toLowerCase(),
            safari = /safari/.test( userAgent ),
            line = /line/.test( userAgent ),
            ios = /iphone|ipod|ipad/.test( userAgent );
            
                if( ios || line) {

                    if ( !standalone && safari ) {
                        if(line)
                        {
                            var sURL = unescape("'.$redirectUrl.'");
                            window.location.replace(sURL);
                        }else
                        {
                             window.close();
                        }
                    
                    } else if ( standalone && !safari ) {
                         window.close();
                    } else if ( !standalone && !safari ) {
                         var sURL = unescape("'.$redirectUrl.'");
                        window.location.replace(sURL);
                    };
                }else {
                   window.close();
                }';
        echo "<script type=\"text/javascript\">".$jsAction."</script>";

        //return $this->resultPageFactory->create();
    }


}
