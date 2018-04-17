<?php
namespace Astralweb\Sms\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $url;

    public function __construct(){
        $this->url = 'https://api.infobip.com/sms/1/text/single';
        $this->Basic = 'TU9NQTE5OTc6TW9tYTEyMjg=';                  // MOMA1997 / Moma1228
    }

    public function setTo($value=''){
        $this->to = $value;
    }

    public function setText($value=''){
        $this->text = $value;
    }

    public function send(){

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Sms_send.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $data = array();
        $data['from']   = 'Moma';
        $data['to']     = $this->to;    //'+886988918006';
        $data['text']   = $this->text;

        $data = json_encode( $data );

        $header = array();
        $header[] = "Authorization: Basic ".$this->Basic;
        $header[] = "Content-Type: application/json";
        //$header[] = "Content-Length: ".strlen($data);
        $header[] = "Accept: application/json";

        // 送出簡訊前紀錄
        $logger->info('url : '.$this->url);
        $logger->info('header : '.json_encode($header));
        $logger->info('data : '.json_encode($data));

        $s = curl_init();
        curl_setopt($s, CURLOPT_URL,$this->url);
        curl_setopt($s, CURLOPT_HTTPHEADER, $header);
        curl_setopt($s, CURLOPT_POST, 1);
        curl_setopt($s, CURLOPT_POSTFIELDS, $data);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($s);

        // 紀錄回傳內容
        $logger->info($contents);
    }
}