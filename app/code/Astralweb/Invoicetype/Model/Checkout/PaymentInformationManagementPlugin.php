<?php


namespace Astralweb\Invoicetype\Model\Checkout;

use Astralweb\Invoicetype\Model\InvoiceTypeFactory as InvoiceTypeFactory;
use Magento\Customer\Model\Session;

class PaymentInformationManagementPlugin
{

    const INVOICE_TYPE_TWO = 'two';
    const INVOICE_TYPE_THREE = 'three';

    /** @var \Astralweb\Invoicetype\Model\InvoiceTypeFactory $_invoiceType */
    protected $_invoiceType;
    /** @var \Magento\Sales\Model\OrderFactory $orderFactory */
    protected $orderFactory;

    protected $session;

    /**
     * PaymentInformationManagementPlugin constructor.
     * @param InvoiceTypeFactory $invoiceType
     */
    public function __construct(
        InvoiceTypeFactory $invoiceType,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Session $session
    ) {
        $this->_invoiceType = $invoiceType;
        $this->orderFactory = $orderFactory;
        $this->session = $session;
    }


    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return mixed
     * @throws \Exception
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        /** @param string $invoiceType */
        $invoiceType = null;
        $oldTaxId = null;
        $taxId = null;
        $purchaserName = null;
        $customerId = null;
        // get JSON post data
        $request_body = file_get_contents('php://input');
        // decode JSON post data into array
        $data = json_decode($request_body, true);
        //get invoice type
        if (isset ($data['paymentMethod']['additional_data']['type'])) {
            $invoiceType = $data['paymentMethod']['additional_data']['type'];
        }
           // die($invoiceType);

            //get purchaser's name
            if (isset ($data['paymentMethod']['additional_data']['purchaser_name'])){
                $purchaserName = $data['paymentMethod']['additional_data']['purchaser_name'];
            }
            //get taxid
            if (isset ($data['paymentMethod']['additional_data']['tax_id'])){
                $taxId = $data['paymentMethod']['additional_data']['tax_id'];
            }

//        die($purchaserName);

       // die('doanh');
        // run parent method and capture int $orderId
        $orderId = $proceed($cartId, $paymentMethod, $billingAddress);
        //die($orderId);
        //out function plugin if customer is not login
        //if (!$this->session->isLoggedIn()){
        //    return $orderId;
        //}
        if(!$customerId){
            $customerId = $this->session->getCustomerId();
        }

        try{
            $invoiceTypeObject = $this->_invoiceType->create();

            $invoiceTypeObject->setData('order_id',$orderId);
            $invoiceTypeObject->setData('invoice_type',$invoiceType);
            $invoiceTypeObject->setData('purchaser_name',$taxId);
            $invoiceTypeObject->setData('tax_id',$purchaserName);

            $invoiceTypeObject->save();
           // $this->saveOrderInvoiceType($invoiceTypeObject->getId(),$orderId);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

        return $orderId;
    }

}
