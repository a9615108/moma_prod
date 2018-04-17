<?php

namespace TestVendor\TestPayment\Model;

/**
 * Pay In Store payment method model
 */
class Testpayment extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'testpayment';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;
    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');

         $shippingMethod =  $checkoutSession->getQuote()->getShippingAddress()->getShippingMethod();
          if($shippingMethod == 'collect_store_collect_store'){
            return false;
        }
        return parent::isAvailable($quote);
    }
}
