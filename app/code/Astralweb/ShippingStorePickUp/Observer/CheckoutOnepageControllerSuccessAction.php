<?php
namespace Astralweb\ShippingStorePickUp\Observer;
 
use Magento\Framework\Event\ObserverInterface;

class CheckoutOnepageControllerSuccessAction implements ObserverInterface
{
    /**
    * Injected Dependency Description
    * 
    * @var \\Magento\Sales\Api\Data\OrderInterface
    */
    protected $salesApiDataOrderInterface;

    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $salesApiDataOrderInterface)
    {
        $this->salesApiDataOrderInterface = $salesApiDataOrderInterface;
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if( empty($_COOKIE["shop_id"]) ){
            return;
        }
        $shop_id = $_COOKIE["shop_id"];

        $orderids = $observer->getEvent()->getOrderIds();

        foreach($orderids as $orderid){
            $order = $this->salesApiDataOrderInterface->load($orderid);
        }
        $shipping_method = $order->getShippingMethod();

        if( strpos($shipping_method, 'ShippingStorePickUp') === 0 ){
            $order->setShopId($shop_id);
            $order->save();
        }
    }
}