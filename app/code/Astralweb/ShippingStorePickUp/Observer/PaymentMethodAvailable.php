<?php

namespace Astralweb\ShippingStorePickUp\Observer;
 
use Magento\Framework\Event\ObserverInterface;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->get('Magento\Variable\Model\Variable')->loadByCode('DEBUG_MODEL');
        $DEBUG_MODEL = $model->getName(); 
        if( ! $DEBUG_MODEL ){
            $quote           = $observer->getEvent()->getQuote();

            $shippingMethod = '';
            if($quote && null !== $quote){
                $shippingMethod =  $quote->getShippingAddress()->getShippingMethod() ;
            }

            if( $shippingMethod == 'ShippingStorePickUp_ShippingStorePickUp'
                && $observer->getEvent()->getMethodInstance()->getCode() != "taixinbank"
            ){
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false); //this is disabling the payment method at checkout page
            }
        }

    }
}