<?php


namespace Astralweb\Shippingsf\Model\CashOnDelivery;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Backend\Model\Auth\Session as BackendSession;
use Magento\OfflinePayments\Model\Cashondelivery;


class Available
{

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var BackendSession
     */
    protected $backendSession;
    protected $_checkoutSession;

    /**
     * @param CustomerSession $customerSession
     * @param BackendSession $backendSession
     */
    public function __construct(
        CustomerSession $customerSession,
        BackendSession $backendSession,
        \Magento\Checkout\Model\Session $checkoutSession

    ) {
        $this->customerSession = $customerSession;
        $this->backendSession = $backendSession;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     *
     * @param Cashondelivery $subject
     * @param $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterIsAvailable(Cashondelivery $subject, $result)
    {
        // Do not remove payment method for admin
        if ($this->backendSession->isLoggedIn()) {
            return $result;
        }
        $shippingMethod =  $this->_checkoutSession->getQuote()->getShippingAddress()->getShippingMethod();
        if($shippingMethod == 'collect_store_collect_store'){
            return false;
        }

        return $result;
    }
}
