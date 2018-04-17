<?php
namespace Customer\Account\Helper;

use Magento\TestFramework\ObjectManager;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $data;
    protected $customer;
    protected $url;
    protected $status;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $session
    ) {
        $this->session = $session;
        $this->messageManager = $messageManager;

        $this->status = array(
            'err'       => false,
        );
    }

    public function setCustomer($customer){
        $this->customer = $customer;
    }

    public function setRegisterUrl($url){
        $this->registerUrl = $url;
    }
    public function setLoginUrl($url){
        $this->loginUrl = $url;
    }

    public function setData($data){
        $this->data = $data;
    }

    public function getStatus($key=''){
        if( $key ){
            return $this->status[$key];
        }
        return $this->status;
    }
    public function setStatus($key,$state){
        $this->status[$key] = $state;
    }

    public function register(){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/POS_MEMBER_REGISTER.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        /* $data 要記 log */
        $logger->info($this->data);

        $data = json_encode( $this->data );

        $url = $this->registerUrl;
        $logger->info('url : '.$url);

        $s = curl_init();
        curl_setopt($s,CURLOPT_URL,$url);
        curl_setopt($s, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));
        curl_setopt($s, CURLOPT_POST, 1);
        curl_setopt($s, CURLOPT_POSTFIELDS, $data);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($s);

        /* $contents 要記 log */
        $logger->info($contents);

        /*
            接收後存入 Vip卡號 、 Vip有效日期 、 開卡位置註記 ， 生日 及 性別  。
        */
        $contents_arr = json_decode($contents,true);

        if( empty( $contents_arr['Vip_no'] ) ){
// 註冊時 電話相同不擋
//* // 登入時 姓名+電話 查無此會員 會跑註冊 -> 電話相同要擋
            if( $contents_arr['Message'] == "會員手機已被註冊" ){
                // 登出
                $this->setStatus('err',true);
            }
//*/
            return;
        }

// 更新會員資料
        $customer = $this->customer;

        $need_save = false;

        // 生日
        $pos2dob = substr($contents_arr['Birthday'],0,4) . '-' .
            substr($contents_arr['Birthday'],4,2) . '-' .
            substr($contents_arr['Birthday'],6,2);

        $dob     = $customer->getDob();        // 生日

        if( $dob != $pos2dob ){
            $customer->setDob($pos2dob);
            $need_save = true;
        }

        // 性別
        if( $contents_arr['Sex'] == '女' ){
            $pos2gender = 2;
        }else{
            $pos2gender = 1;
        }

        if( $customer->getGender() != $pos2gender ){
            $customer->setGender($pos2gender);
            $need_save = true;
        }

        // Vip卡號
        if( $customer->getVipNum() != $contents_arr['Vip_no'] ){
            $customer->setVipNum($contents_arr['Vip_no']);
            $need_save = true;
        }

        // Vip有效日期
        $pos2Expired_date = substr($contents_arr['Expired_date'],0,4) . '-' .
            substr($contents_arr['Expired_date'],4,2) . '-' .
            substr($contents_arr['Expired_date'],6,2) . ' 23:59:59';

        if( $customer->getVipDate() != $pos2Expired_date ){
            $customer->setVipDate($pos2Expired_date);
            $need_save = true;
        }

        // 開卡位置註記
        if( $customer->getVipSite() != $contents_arr['Reg_mark'] ){
            $customer->setVipSite($contents_arr['Reg_mark']);
            $need_save = true;
        }

        // 手機
        if( $customer->getVipPhone() != $contents_arr['Mobile'] ){
            $customer->setVipPhone($contents_arr['Mobile']);
            $need_save = true;
        }

        if( $need_save ){
            $customer->save();
        }
    }

    public function login(){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/POS_MEMBER_LOGIN.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        /* $data 要記 log */
        $logger->info('login ==============================');

        $url = $this->loginUrl;
        $logger->info('url : '.$url);

        $logger->info($this->data);
        $data = json_encode( $this->data );

        $s = curl_init();
        curl_setopt($s, CURLOPT_URL,$url);
        curl_setopt($s, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));
        curl_setopt($s, CURLOPT_POST, 1);
        curl_setopt($s, CURLOPT_POSTFIELDS, $data);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($s);

        /* $contents 要記 log */
        $logger->info($contents);

        // 接收後， 生日 、 性別 、 Vip卡號 、 Vip有效日期 這4項若2邊資料不同，以凱新這邊資料為主，歐斯瑞更新資料。
        $contents_arr = json_decode($contents,true);

        // 若失敗要跑註冊
        if( ! isset($contents_arr['Vip_no']) ){
            $logger->info('login error ==============================');

            $this->register();
        }
        else{
            $need_save = false;

            $customer_eav = $this->customer;

            // 生日
            $pos2dob = substr($contents_arr['Birthday'],0,4) . '-' .
                substr($contents_arr['Birthday'],4,2) . '-' .
                substr($contents_arr['Birthday'],6,2);

            $dob    = $customer_eav->getDob();      // 生日
            if( $dob != $pos2dob ){
                $customer_eav->setDob($pos2dob);
                $need_save = true;
            }

            // 性別
            if( $contents_arr['Sex'] == '女' ){
                $pos2gender = 2;
            }else{
                $pos2gender = 1;
            }

            $gender = $customer_eav->getGender();
            if( $gender != $pos2gender ){
                $customer_eav->setGender($pos2gender);
                $need_save = true;
            }

            // Vip卡號
            if( $customer_eav->getVipNum() != $contents_arr['Vip_no'] ){
                $customer_eav->setVipNum($contents_arr['Vip_no']);
                $need_save = true;
            }

            // Vip有效日期
            if( $customer_eav->getVipDate() != $contents_arr['Expired_date'] ){
                $customer_eav->setVipDate($contents_arr['Expired_date']);
                $need_save = true;
            }

            // 開卡位置註記
            if( $customer_eav->getVipSite() != $contents_arr['Reg_mark'] ){
                $customer_eav->setVipSite($contents_arr['Reg_mark']);
                $need_save = true;
            }

            if( $need_save ){
                $customer_eav->save();
            }
        }
    }
}