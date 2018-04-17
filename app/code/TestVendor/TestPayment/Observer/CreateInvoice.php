<?php
namespace TestVendor\TestPayment\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class CreateInvoice implements ObserverInterface{
    protected $_orderRepository;
 
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;
    protected $logger;
 
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;
    protected $_invoiceSender;

    public function __construct(
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        $this->_invoiceSender = $invoiceSender;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->logger = $logger;
    }

 public function execute(Observer $observer)
    {
    $order = $observer->getEvent()->getOrder();
    $payment = $order->getPayment();
    $paymentMethod = $payment->getMethodInstance()->getCode();

    if($paymentMethod == 'testpayment'){
            if ($order->canInvoice()) {
                // Create invoice for this order
                $invoice = $this->_invoiceService->prepareInvoice($order);
                // Make sure there is a qty on the invoice
                if (!$invoice->getTotalQty()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You can\'t create an invoice without products.')
                    );
                }

                // Register as invoice item
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                


                // Save the invoice to the order
                $transactionSave = $this->_transaction->addObject($invoice)
                                                      ->addObject($invoice->getOrder());

                $transactionSave->save();

                
                // Magento\Sales\Model\Order\Email\Sender\InvoiceSender
                $this->_invoiceSender->send($invoice);
                $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId()))
                    ->setIsCustomerNotified(true)
                    ->save();

                
    }

    }


}
}