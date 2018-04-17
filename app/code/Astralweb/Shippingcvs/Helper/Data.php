<?php
namespace Astralweb\Shippingcvs\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_resource;
    protected $_directory;
    protected $_orderRepository;
    protected $_ioFile;
    protected $_order;
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,\Magento\Framework\App\Filesystem\DirectoryList $directory_list,
                                \Magento\Sales\Api\Data\OrderInterface $order,
                                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                \Magento\Framework\Filesystem\Io\File $ioFile,
                                \Magento\Framework\App\ResourceConnection $resource)
    {
        $this->_directory = $directory_list;
        $this->_resource = $resource;
        $this->scopeConfig = $scopeConfig;
        $this->_orderRepository = $orderRepository;
        $this->_ioFile = $ioFile;
        $this->_order = $order;
    }
    public function cvsapi($Incrementid){
        $order = $this->_order->loadByIncrementId($Incrementid);
        $shippingAdress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $shiptoName = $shippingAdress->getData('firstname').$shippingAdress->getData('lastname');
        $shiptoPhone = $shippingAdress->getData('telephone');
        $subtotal = (int) $order->getGrandTotal();
        $storePhone = $this->getStorePhone();
        $phoneBilling = $billingAddress->getData('telephone');
        $paymentMethodcode = $order->getPayment()->getMethod();
        if($paymentMethodcode == 'taixinbank'){
            $AMT = 0;
        }else{
            $AMT = $subtotal;
        }
        if($phoneBilling !== ''){
            $threelastPhone = substr($phoneBilling,-3);
        }else{
            $threelastPhone= '';
        }


        $ECNO = '248';//網站代號
        if(strlen($Incrementid) == 10){
            $Incrementidpost = '0'.$Incrementid;
        }
        $ODNO = $Incrementidpost;
        //Get STNO from table astralweb_shippingcvs 
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('astralweb_shippingcvs');
        $sql =  "SELECT cvsspot FROM " . $tableName ." WHERE increment_id = ".$Incrementid;
        $result = $connection->fetchAll($sql);
        //var_dump($result[0]['cvsspot']);die;

        $STNO = $result[0]['cvsspot']; //店鋪編號 convenience store number
        $PRODNM = '0';//商品別代碼 0 : 一般商品 1 : 票券商品
        if($AMT == 0){
            $TRADETYPE=3;
        }else{
            $TRADETYPE=1;
        }
        $SERCODE = '990';//代收代號
        $EDCNO = 'D04';//大物流代碼
        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>
            <ORDER_DOC>
                <ORDER>
                    <ECNO>'.$ECNO.'</ECNO>
                    <ODNO>'.$ODNO.'</ODNO>
                    <STNO>'.$STNO.'</STNO>
                    <AMT>'.$AMT.'</AMT>
                    <CUTKNM>'.$shiptoName.'</CUTKNM>
                    <CUTKTL>'.$threelastPhone.'</CUTKTL>
                    <PRODNM>'.$PRODNM.'</PRODNM>
                    <ECWEB><![CDATA[MOMA購物網]]></ECWEB>
                    <ECSERTEL>'.$storePhone.'</ECSERTEL>
                    <REALAMT>'.$subtotal.'</REALAMT>
                    <TRADETYPE>'.$TRADETYPE.'</TRADETYPE>
                    <SERCODE>'.$SERCODE.'</SERCODE>
                    <EDCNO>'.$EDCNO.'</EDCNO>
                </ORDER>
                <ORDERCOUNT>
                    <TOTALS>1</TOTALS>
                </ORDERCOUNT>
            </ORDER_DOC>';
        // var_dump($xmlStr);echo "<br>";
        // print_r($xmlStr);die;
        $opts = array(
            'http'=>array(
                'user_agent' => 'PHPSoapClient'
            )
        );

        $context = stream_context_create($opts);
        $client = new \SoapClient("http://cvsweb.cvs.com.tw/webservice/service.asmx?wsdl",array('stream_context' => $context, 'cache_wsdl' => WSDL_CACHE_NONE));
        $parameters = new \stdClass();
        $parameters->xmlStr = $xmlStr;
        $result = $client->ORDERS_ADD($parameters);
        $resultfinal =  $result->ORDERS_ADDResult;
        //$resultfinal = str_replace("成功0筆(含異常0筆)，踼退1筆，合計1筆", "", $resultfinal);
        return $resultfinal;




    }
    public function getFileF04(){
        $file_name = 'F04248CVS'.date("Ymd").'.xml';
        $dir = './F04/';
        $foderPub = $this->_directory->getPath('pub').'/F04/';
        $filenamefinal = $foderPub.$file_name;
        $file = fopen($filenamefinal, 'w');
        $user = '248';
        $pass = 'Yd$3@^PBnTnCkqNj';
        $ftp_server = 'ftp://cvsftp.cvs.com.tw'.$dir.$file_name;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ftp_server);
        curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$pass);
// curl_setopt($ch, CURLOPT_HEADER, "Content-Type:application/xml");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
        curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $data = curl_exec($ch);
        fwrite($file, $data);
// $error_no = curl_errno($ch);

        curl_close($ch);
        fclose($file);
        return $filenamefinal;

    }
    public function getFileF05(){
        $file_name = 'F05248CVS'.date("Ymd").'.xml';

        $dir = './F05/';

        $foderPub = $this->_directory->getPath('pub').'/F05/';
        $filenamefinal = $foderPub.$file_name;
        $file = fopen($filenamefinal, 'w');
        $user = '248';
        $pass = 'Yd$3@^PBnTnCkqNj';

        $ftp_server = 'ftp://cvsftp.cvs.com.tw'.$dir.$file_name;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ftp_server);
        curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$pass);
// curl_setopt($ch, CURLOPT_HEADER, "Content-Type:application/xml");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
        curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $data = curl_exec($ch);
        fwrite($file, $data);
// $error_no = curl_errno($ch);

        curl_close($ch);
        fclose($file);
        return $filenamefinal;

    }
    public function getFileF07(){
        $file_name = 'F07248CVS'.date("Ymd").'.xml';

        $dir = './F07/';
        $foderPub = $this->_directory->getPath('pub').'/F07/';
        $filenamefinal = $foderPub.$file_name;
        $file = fopen($filenamefinal, 'w');
        $user = '248';
        $pass = 'Yd$3@^PBnTnCkqNj';

        $ftp_server = 'ftp://cvsftp.cvs.com.tw'.$dir.$file_name;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ftp_server);
        curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$pass);
// curl_setopt($ch, CURLOPT_HEADER, "Content-Type:application/xml");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
        curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $data = curl_exec($ch);
        fwrite($file, $data);
// $error_no = curl_errno($ch);

        curl_close($ch);
        fclose($file);
        return $filenamefinal;
    }
    public function getFileF09(){
        $file_name = 'F09248CVS'.date("Ymd").'.xml';
        $dir = './F09/';
        $foderPub = $this->_directory->getPath('pub').'/F09/';
        $filenamefinal = $foderPub.$file_name;
        $file = fopen($filenamefinal, 'w');
        $user = '248';
        $pass = 'Yd$3@^PBnTnCkqNj';

        $ftp_server = 'ftp://cvsftp.cvs.com.tw'.$dir.$file_name;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ftp_server);
        curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$pass);
// curl_setopt($ch, CURLOPT_HEADER, "Content-Type:application/xml");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
        curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $data = curl_exec($ch);
        fwrite($file, $data);
// $error_no = curl_errno($ch);

        curl_close($ch);
        fclose($file);

        return $filenamefinal;
    }
    public function getFileF01(){
        $file_name = 'F01ALLCVS'.date("Ymd").'.xml';
        $dir = './F01/';
        $foderPub = $this->_directory->getPath('pub').'/F01/';
        $filenamefinal = $foderPub.$file_name;
        if(!file_exists($filenamefinal) || file_get_contents($filenamefinal) == ''){
            $file = fopen($filenamefinal, 'w');
            $user = '248';
            $pass = 'Yd$3@^PBnTnCkqNj';

            $ftp_server = 'ftp://cvsftp.cvs.com.tw'.$dir.$file_name;
            header("Content-Type: text/html; charset=big5");
            $ch = curl_init();
            //header('Content-Type: text/html; charset=utf-8');

            curl_setopt($ch, CURLOPT_URL, $ftp_server);
            curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$pass);
// curl_setopt($ch, CURLOPT_HEADER, "Content-Type:application/xml");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
            curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

            $data = curl_exec($ch);
            fwrite($file, $data);
// $error_no = curl_errno($ch);

            curl_close($ch);
            fclose($file);

        }
        return $filenamefinal;



    }
    public function getStorePhone()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/phone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getDomainName()
    {
        return $this->scopeConfig->getValue(
            'carriers/collect_storecvs/namedomain',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getUserNameSMS()
    {
        return $this->scopeConfig->getValue(
            'carriers/collect_storecvs/usernameaccoutsms',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPasswordSMS()
    {
        return $this->scopeConfig->getValue(
            'carriers/collect_storecvs/passwordaccoutsms',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTextSmsCreateShipment()
    {
        return $this->scopeConfig->getValue(
            'carriers/collect_storecvs/smscreateshipment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getTextSmsPackageArrived($storename)
    {
        $textSms = $this->scopeConfig->getValue(
            'carriers/collect_storecvs/smspackagearrived',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return str_replace("{{store_name}}",$storename,$textSms);
    }
    public function getTextSmsPackageThree($storename)
    {
        $textSms = $this->scopeConfig->getValue(
            'carriers/collect_storecvs/smspackagethree',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return str_replace("{{store_name}}",$storename,$textSms);
    }
    public function getCountryCodePhone($countryId){
        $json = '{"BD": "880", "BE": "32", "BF": "226", "BG": "359", "BA": "387", "BB": "+1-246", "WF": "681", "BL": "590", "BM": "+1-441", "BN": "673", "BO": "591", "BH": "973", "BI": "257", "BJ": "229", "BT": "975", "JM": "+1-876", "BV": "", "BW": "267", "WS": "685", "BQ": "599", "BR": "55", "BS": "+1-242", "JE": "+44-1534", "BY": "375", "BZ": "501", "RU": "7", "RW": "250", "RS": "381", "TL": "670", "RE": "262", "TM": "993", "TJ": "992", "RO": "40", "TK": "690", "GW": "245", "GU": "+1-671", "GT": "502", "GS": "", "GR": "30", "GQ": "240", "GP": "590", "JP": "81", "GY": "592", "GG": "+44-1481", "GF": "594", "GE": "995", "GD": "+1-473", "GB": "44", "GA": "241", "SV": "503", "GN": "224", "GM": "220", "GL": "299", "GI": "350", "GH": "233", "OM": "968", "TN": "216", "JO": "962", "HR": "385", "HT": "509", "HU": "36", "HK": "852", "HN": "504", "HM": " ", "VE": "58", "PR": "+1-787 and 1-939", "PS": "970", "PW": "680", "PT": "351", "SJ": "47", "PY": "595", "IQ": "964", "PA": "507", "PF": "689", "PG": "675", "PE": "51", "PK": "92", "PH": "63", "PN": "870", "PL": "48", "PM": "508", "ZM": "260", "EH": "212", "EE": "372", "EG": "20", "ZA": "27", "EC": "593", "IT": "39", "VN": "84", "SB": "677", "ET": "251", "SO": "252", "ZW": "263", "SA": "966", "ES": "34", "ER": "291", "ME": "382", "MD": "373", "MG": "261", "MF": "590", "MA": "212", "MC": "377", "UZ": "998", "MM": "95", "ML": "223", "MO": "853", "MN": "976", "MH": "692", "MK": "389", "MU": "230", "MT": "356", "MW": "265", "MV": "960", "MQ": "596", "MP": "+1-670", "MS": "+1-664", "MR": "222", "IM": "+44-1624", "UG": "256", "TZ": "255", "MY": "60", "MX": "52", "IL": "972", "FR": "33", "IO": "246", "SH": "290", "FI": "358", "FJ": "679", "FK": "500", "FM": "691", "FO": "298", "NI": "505", "NL": "31", "NO": "47", "NA": "264", "VU": "678", "NC": "687", "NE": "227", "NF": "672", "NG": "234", "NZ": "64", "NP": "977", "NR": "674", "NU": "683", "CK": "682", "XK": "", "CI": "225", "CH": "41", "CO": "57", "CN": "86", "CM": "237", "CL": "56", "CC": "61", "CA": "1", "CG": "242", "CF": "236", "CD": "243", "CZ": "420", "CY": "357", "CX": "61", "CR": "506", "CW": "599", "CV": "238", "CU": "53", "SZ": "268", "SY": "963", "SX": "599", "KG": "996", "KE": "254", "SS": "211", "SR": "597", "KI": "686", "KH": "855", "KN": "+1-869", "KM": "269", "ST": "239", "SK": "421", "KR": "82", "SI": "386", "KP": "850", "KW": "965", "SN": "221", "SM": "378", "SL": "232", "SC": "248", "KZ": "7", "KY": "+1-345", "SG": "65", "SE": "46", "SD": "249", "DO": "+1-809 and 1-829", "DM": "+1-767", "DJ": "253", "DK": "45", "VG": "+1-284", "DE": "49", "YE": "967", "DZ": "213", "US": "1", "UY": "598", "YT": "262", "UM": "1", "LB": "961", "LC": "+1-758", "LA": "856", "TV": "688", "TW": "886", "TT": "+1-868", "TR": "90", "LK": "94", "LI": "423", "LV": "371", "TO": "676", "LT": "370", "LU": "352", "LR": "231", "LS": "266", "TH": "66", "TF": "", "TG": "228", "TD": "235", "TC": "+1-649", "LY": "218", "VA": "379", "VC": "+1-784", "AE": "971", "AD": "376", "AG": "+1-268", "AF": "93", "AI": "+1-264", "VI": "+1-340", "IS": "354", "IR": "98", "AM": "374", "AL": "355", "AO": "244", "AQ": "", "AS": "+1-684", "AR": "54", "AU": "61", "AT": "43", "AW": "297", "IN": "91", "AX": "+358-18", "AZ": "994", "IE": "353", "ID": "62", "UA": "380", "QA": "974", "MZ": "258"}
        ';
        $jsonArr = json_decode($json, true);
        return $jsonArr[$countryId];

    }
    public function SendSMSApi($textSms,$tel){
        $username = $this->getUserNameSMS();
        $password = $this->getPasswordSMS();
        $token = base64_encode($username.':'.$password);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.infobip.com/sms/1/text/single",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{ \"from\":\"InfoSMS\", \"to\":\"".$tel."\", \"text\":\"".$textSms."\" }",
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Basic ".$token,
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        // if ($err) {
        // echo "cURL Error #:" . $err;
        // } else {
        // echo $response;
        // }
    }
    public function WritelogCVS($date,$incrementId,$action,$request,$response,$status){
        $foderlogCVS = $this->_directory->getPath('var').'/log/cvs/';
        $filename = $foderlogCVS.$incrementId.'.log';
        if(!is_dir($foderlogCVS)) $this->_ioFile->mkdir($foderlogCVS, 0777);
        $filelogcvs= fopen($filename, "a+");
        $textlog = $date." ".$incrementId." ".$action." ".$request." ".$response." ".$status."\n";
        fwrite($filelogcvs, $textlog);
        fclose($filelogcvs);
    }

    public function getBarcodeSection($orderid){
        $arr =[
            0=>'0',1=>'1',2=>'2',3=>'3',4=>'4',5=>'5',6=>'6',7=>'7',8=>'8',9=>'9',
            10=>'A',11=>'B',12=>'C',13=>'D',14=>'E',15=>'F',16=>'G',17=>'H',18=>'I',19=>'J',
            20=>'K',21=>'L',22=>'M',23=>'N',24=>'O',25=>'P',26=>'Q',27=>'R',28=>'S',29=>'T',
            30=>'U',31=>'V',32=>'W',33=>'X',34=>'Y',35=>'Z',36=>'-',37=>'.',38=>' ',39=>'$',
            40=>'/',41=>'+',42=>'%'
        ];

        $stringBarcode = '';
        $stringBarcode1 = '';
        $stringBarcode2 = '';
        $stringBarcode3 = '';
        $stringBarcode4 = '';
        $SERCODE = '990';
        $paymentMethod ='3';
        $shortTerm = '';
        $textPickup1 ='';
        $textPickup2 ='';
        $filePickup ='';
        $order = $this->_orderRepository->get($orderid);
        $paymentMethodcode = $order->getPayment()->getMethod();
        if($paymentMethodcode == 'taixinbank'){
            $paymentAmount = "00000";
            $textPickup1 = '取貨不付款';
            $textPickup2 = '需核對證件';
        }else{
            $grandTotal = (int) $order->getGrandTotal();
            if(strlen($grandTotal) >= 5){
                $paymentAmount = substr($grandTotal,-5);
            }else{
                $paymentAmountzero = '';
                for($i = 0 ; $i < (5 - strlen($grandTotal)) ; $i++){
                    $paymentAmountzero .='0';
                }
                $paymentAmount = $paymentAmountzero.$grandTotal;
            }
            $paymentMethod = 1;
            $textPickup1 = '取貨付款';
            $textPickup2 = '不需核對證件';
        }
        $orderIncrement = $order->getIncrementId();
        $orderIncrementpost = '0'.$orderIncrement;
        $threeFirstOrderPost = substr($orderIncrementpost,0,3);
        $eightLastOrderPost = substr($orderIncrementpost,-8);
        $connection = $this->_resource->getConnection();
        $table  = $this->_resource->getTableName('astralweb_shippingcvs');
        $sql = "SELECT * FROM ".$table." WHERE increment_id = ".$orderIncrement;
        $result = $connection->fetchAll($sql);
        if(count($result) > 0){
            $storeId = $result[0]['cvsspot'];
            $dcrono = $result[0]['cvsnum'];
            $status = $result[0]['status'];
            if($status == 1){
                $filePickup = 'D10';
            }elseif ($status == 2){
                $filePickup = 'D04';
            }elseif ($status == 3){
                $filePickup = 'D05';
            }elseif ($status == 4){
                $filePickup = 'D07';
            }elseif ($status == 5){
                $filePickup = 'D09';
            }elseif ($status == 0) {
                $filePickup = 'D00';
            }
            $storeIdFirst = strtolower(substr($storeId,0,1));
            if($storeIdFirst == 'f'){
                $stringBarcode1.='1';
                $stringBarcode2.='1';
                $shortTerm = '翊';

            }elseif($storeIdFirst == 'l'){
                $stringBarcode1.='2';
                $stringBarcode2.='2';
                $shortTerm = '萊';
            }elseif ($storeIdFirst == 'k'){
                $stringBarcode1.='3';
                $stringBarcode2.='3';
                $shortTerm = 'K';
            }
            $ecsuppliercode = '248';
            $stringBarcode1.=$ecsuppliercode;
            $stringBarcode1.='00';
            $stringBarcode1.=$orderIncrementpost;
            $sumBarcode = 0;
            for($i=0;$i<strlen($stringBarcode1);$i++){
                $sumBarcode += (int)$stringBarcode1[$i];
            }
            $mod = $sumBarcode%43;
            $stringBarcode1.=$arr[$mod];
            $stringBarcode2.=$dcrono;
            $stringBarcode3.='248'.$threeFirstOrderPost.$SERCODE;
            $stringBarcode4 .=$eightLastOrderPost.$paymentMethod.$paymentAmount;
            $sumBarcode3Even=0;
            $sumBarcode3Odd=0;
            for($j=0;$j<strlen($stringBarcode3);$j++){
                if($j%2 == 0){
                    $sumBarcode3Even += (int)$stringBarcode3[$j];
                }else{
                    $sumBarcode3Odd += (int)$stringBarcode3[$j];
                }
            }
            $sumBarcode4Even = 0;
            $sumBarcode4Odd = 0;
            for($k=0;$k<strlen($stringBarcode4);$k++){
                if($k%2 == 0){
                    $sumBarcode4Even += (int)$stringBarcode4[$k];
                }else{
                    $sumBarcode4Odd += (int)$stringBarcode4[$k];
                }
            }
            $character1 = ($sumBarcode3Even+$sumBarcode4Even)%11;
            if($character1 == 10) $character1=1;
            $character2 = ($sumBarcode3Odd+$sumBarcode4Odd)%11;
            if($character2 == 0){
                $character2=8;
            }elseif($character2 == 10){
                $character2 =9;
            }
            $stringBarcode4.=$character1.$character2;
        }

        $stringBarcode = $stringBarcode1.','.$stringBarcode2.','.$stringBarcode3.','.$stringBarcode4.','.$shortTerm.','.$storeId.','.$dcrono.','.$textPickup1.','.$textPickup2.','.$filePickup;
        return $stringBarcode;
    }
    public function getPickupHtml($position,$orderid){
        $datePickup =date("y/m/d", time());
        $datePickupAfter = date("y/m/d", time() + 86400*7);
        $datePickupRight = substr($datePickup,3);
        $stringBarcode = $this->getBarcodeSection($orderid);
        $arrBarcode = explode(',',$stringBarcode);
        $barcode1 = $arrBarcode[0];
        $barcode2 = $arrBarcode[1];
        $barcode3 = $arrBarcode[2];
        $barcode4 = $arrBarcode[3];
        $shortTerm =$arrBarcode[4];
        $storeId = $arrBarcode[5];
        $dcrono = $arrBarcode[6];
        $textPickup1 = $arrBarcode[7];
        $textPickup2 = $arrBarcode[8];
        $filePickup  = $arrBarcode[9];
        $namestore = '';
        $textBarcode3 =  substr($barcode3,0,3).' '.substr($barcode3,3,3).' '.substr($barcode3,6,3);
        $textBarcode4 = substr($barcode4,0,4).' '.substr($barcode4,4,4).' '.substr($barcode4,8,4).' '.substr($barcode4,12,4);
        $order = $this->_orderRepository->get($orderid);
        $createdAt = $order->getCreatedAt();
        $newDate = date("Y-m-d", strtotime($createdAt));
        $newDate = str_replace('-','',$newDate);
        $namefilexml = 'F01ALLCVS'.$newDate.'.xml';
        $xmlPath = $this->_directory->getPath('pub').'/F01/'.$namefilexml;
        $xml = simplexml_load_file($xmlPath);
        $contentsF01 = $xml->xpath('F01CONTENT');
        foreach ($contentsF01 as $content){
            if($content->STNO == $storeId){
                $namestore = $content->STNM;
            }

        }

        $incrementId = $order->getIncrementId();
        $shippingAdress = $order->getShippingAddress();
        $billingAdress = $order->getBillingAddress();
        $nameReciver = $shippingAdress->getData('firstname').' '.$shippingAdress->getData('lastname');
        $phoneBilling = $billingAdress->getData('telephone');
        $threelastPhone = substr($phoneBilling,-3);

        $style='';
        if($position == 2){
            $style = 'float: right';
        }elseif ($position == 3){
            $style = 'margin-top: 7px';
        }elseif ($position == 4){
            $style = 'float: right';
        }
        $htmlPickup ='
            <div class="pickup" style="'.$style.'">

            <div class="pickup__main" style="">
                <div class="table-pickup__top">
                    <div class="content-main">
                        <p class="_title">店編'.$storeId.'</p>
                        <div class="img">
                            <barcode class="barcode" code="'.$barcode1.'" type="C39" class="barcode" height="1.6" size="0.6" style="margin-right:10px;"/>
                            <p class="barcode-number">*'.$barcode1.'*</p>
                            <p class="barcode-text" style="font-family:kaiu;weight:bold;font-size:20pt">'.$namestore.'</p>
                        </div>
                        <div class="pickup-content">
                            <p>訂單編號: 0'.$incrementId.'</p>
                            <p>商品進店日: '.$datePickup.'</p>
                            <p>預計退貨日: '.$datePickupAfter.'</p>
                        </div>
                    </div>

                    <div class="content-right">
                        <div class="label-top"><div>'.$shortTerm.'</div></div>
                        <div class="img-barcode" style="margin-left: 5px;">
                            <barcode class="barcode" code="'.$barcode2.'" type="C39" class="barcode" height="1.8" size="0.8" style="margin-left: 0px;margin-right:14px;margin-top:10px;"/>
                           
                        </div>
                         <h1 style="float:left;text-align: left;">'.$dcrono.'</h1>  
                    </div>
                </div>

                <div class="table-pickup__middle" style="">

                    <div class="table-pickup__middle--inner">

                        <div class="table-pickup__middle--left">
                            <p class="table-pickup__middle--left__title">提貨人:  '.$nameReciver.'</p>
                            <div class="img-barcode-1">
                            <barcode class="barcode" code="'.$barcode3.'" type="C39" class="barcode" height="1.6" size="0.6" style="margin-bottom: 5px"/>
                                <p>'.$textBarcode3.'</p>
                            </div>
                            <div class="img-barcode-2"> 
                            <barcode code="'.$barcode4.'" type="C39" class="barcode" height="1.7" size="0.55" style="margin-bottom: 7px;margin-left:14px;"/>
                                <p>'.$textBarcode4.'</p>
                            </div>  
                        </div>

                        <div class="table-pickup__middle--right">
                            <p class="line-1">手機末3碼</p>
                            <p class="line-2" style="font-family:kaiu;weight:bold;font-size:20pt">'.$threelastPhone.'</p>
                            <p class="line-3">(您的手機末三碼為'.$threelastPhone.'請檢視)</p>
                            <div class="line-4" style="">
                                <p>'.$textPickup1.'</p>
                                <p>'.$textPickup2.'</p>
                            </div>
                        </div>

                    </div>  
                    
                </div>

                <div class="table-pickup__bottom">
                    <div class="table-pickup__bottom-title">
                        <div>
                            D04
                        </div>
                     </div>

                    <div class="content">
                        <p>供應商名稱(代號): 248</p>
                        <p>網站名稱+網址: MOMA - https://www.moma1997.com</p>
                    </div>
                    
                </div>

            </div>
            
            <div class="pickup__right">
                <div class="pickup__right--inner">
                    <div class="right-date">'.$datePickupRight.'</div>
                    <div class="right-title">M<br>O<br>M<br>A</div>
                    <div class="right-sub-title" style="font-family:kaiu;weight:bold;font-size:20pt">'.$nameReciver.'</div>
                </div>    
            </div>
        
        </div>
        ';
        return $htmlPickup;
    }

    public function getHtml($htmlPickup){
        $html='
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8" />
        <title>Label</title>
        <link rel="stylesheet" href="styles-pickup.css">
    </head>

    <body style="font-family: BIG5;margin: 0 auto">
    '.$htmlPickup.'
    </body>
</html> ';

        return $html;
    }



}
