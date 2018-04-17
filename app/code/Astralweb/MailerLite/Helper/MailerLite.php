<?php
namespace Astralweb\MailerLite\Helper;

class MailerLite extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_KEY   = 'mailerlite/subscribers/apikey';
    const GROUP_ID  = 'mailerlite/subscribers/groupid';
    const TYPE_ID  = 'mailerlite/subscribers/type';

    const TYPE_ACTIVE = 'active';
    const TYPE_UNSUBSCRIBED = 'unsubscribed';


    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);

    }

    private function getParams($selector){
        return $this->scopeConfig->getValue($selector);
    }

    private function getAPIKey() {
        return $this->getParams(self::API_KEY);
    }

    public function getGroupId() {
        return $this->getParams(self::GROUP_ID);
    }

    public function getType() {
        return $this->getParams(self::TYPE_ID);
    }

    public function getGroups(){
        $apikey = $this->getAPIKey();
        if($apikey){
            return $this->_getGroups($apikey);
        }
        else{
            return [];
        }
    }
    public function getTypes() {
        return [
            'active'=> __('Active'),
            'unsubscribed'=> __('Unsubscribed'),
            'bounced'=> __('Bounced'),
            'unconfirmed'=> __('Unconfirmed')
        ];
    }

    /**
     * @param $data object
     * @return mixed|string json
     */
    public function addSubscribersToGroup($data){
        $apikey = $this->getAPIKey();
        $groupid = $this->getGroupId();
        $res = $this->_addSubscribersToGroup($data, $apikey, $groupid);
        return $res;
    }

    /**
     * @param $APIKey
     * @return mixed|string json
     */
    private function _getGroups($APIKey)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://api.mailerlite.com/api/v2/groups",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "x-mailerlite-apikey: " . $APIKey
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }
    private function _getGroup($APIKey, $groupId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://api.mailerlite.com/api/v2/groups/" . $groupId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "x-mailerlite-apikey: " . $APIKey
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
    /**
     * @param $APIKey
     * @return mixed|string json
     */
    private function _getStats($APIKey)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://api.mailerlite.com/api/v2/stats",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "x-mailerlite-apikey: " . $APIKey
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    /**
     * @param $data object
     * @param $APIKey string
     * @param $groupId integer
     * @return mixed|string
     */
    private function _addSubscribersToGroup($data, $APIKey, $groupId)
    {
        $curl = curl_init();
        $jsonData = json_encode($data);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.mailerlite.com/api/v2/groups/". $groupId ."/subscribers",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "x-mailerlite-apikey: " . $APIKey
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}
