<?php

namespace Astralweb\Iguang\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class ControllerActionLayoutRenderBefore implements ObserverInterface {

    protected $_order;

    /**
     * fires when sales_order_save_after is dispatched
     * 
     * @param Observer $observer
     */
    public function execute(Observer $observer) {

        function fetch_GET($key=''){
            return isset($_GET[$key])?$_GET[$key]:'';
        }

        $ecid       = fetch_GET('ecid');
        if( $ecid ){

            $utm_source = fetch_GET('utm_source');
            $utm_medium = fetch_GET('utm_medium');

            // 24 小時 = 86400 秒
            setcookie('utm_source', $utm_source, time()+86400);
            setcookie('utm_medium', $utm_medium, time()+86400);
            setcookie('ecid'      , $ecid      , time()+86400);
        }
    }
}