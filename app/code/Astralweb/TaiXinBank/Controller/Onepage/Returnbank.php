<?php

namespace Astralweb\TaiXinBank\Controller\Onepage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Checkout\Model\Cart as CustomerCart;


class Returnbank extends \Magento\Framework\App\Action\Action  {

    protected $request;
    protected $cart;
    protected $_customerSession;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Request\Http $request,
        InvoiceSender $invoiceSender,
        \Magento\Sales\Api\Data\OrderInterface $order,

        \Magento\Customer\Model\Session $customerSession,
        CustomerCart $cart
    )
    {
        $this->request = $request;
        $this->invoiceSender = $invoiceSender;
            $this->order = $order;

        $this->cart = $cart;
        $this->_customerSession = $customerSession;
        parent::__construct($context);

    }

    public function getPost()
    {
        return $this->request->getPost();
    }

    public function execute()
    {
        $parmas = $this->getPost();

        $order = $this->order->loadByIncrementId($parmas['order_no']);

        $data_response = $parmas;
        //var_dump($parmas);die;
         $this->directory_list = $this->_objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');

            $folderExist = is_dir($this->directory_list->getPath('var') . '/log/taixinbank/');
            if (!$folderExist) {
                mkdir($this->directory_list->getPath('var') . '/log/taixinbank/', 0755, true);
            }
            file_put_contents($this->directory_list->getPath('var') . '/log/taixinbank/'.$parmas['order_no'].'_response_'. date('Y-m-d H:i:s') .'.log', json_encode($data_response), FILE_APPEND);

        if($parmas['ret_code'] == "00"){

            if ($order->canInvoice()) {
                // Create invoice for this order


                $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);

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
                $transaction = $this->_objectManager->create('Magento\Framework\DB\Transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

                $transaction->save();

                // Magento\Sales\Model\Order\Email\Sender\InvoiceSender
                $this->invoiceSender->send($invoice);

                $order->addStatusHistoryComment(
                    __($parmas['ret_msg'])
                )
                    // ->setIsCustomerNotified(true)
                    ->save();

                if (isset($parmas['last_4_digit_of_pan']) && $parmas['last_4_digit_of_pan']){
                    $order->setLast4DigitOfPan($parmas['last_4_digit_of_pan']);
                }
                if (isset($parmas['first_6_digit_of_pan']) && $parmas['first_6_digit_of_pan']){
                    $order->setFirst6DigitOfPan($parmas['first_6_digit_of_pan']);
                }
                $order->setState('processing');
                $order->setStatus('processing');
                $order->save();
                $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $resultRedirect = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore($storeManager->getStore()->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB).'checkout/onepage/success';
                // $this->deleteQuoteItems();
                return $this->_redirect($resultRedirect);
            }

        }else{
        return $this->_redirect('astralwebtaixinbankpayment/index/bankfailt');
        }
    }

    public function deleteQuoteItems(){
        $checkoutSession = $this->getCheckoutSession();
        $quote_Id= $this->cart->getQuote()->getId();

        $allItems = $checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($allItems as $item) {
            $itemId = $item->getItemId();
            $quoteItem = $this->getItemModel()->load($itemId);
            $quoteItem->delete();
        }
        if(!empty($quote_Id)){
            // $quoteModel = $objectManager->create('Magento\Quote\Model\Quote');
            // $quoteModel->delete($quote_Id);
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('quote');
            $sql = "DELETE  FROM " . $tableName." WHERE entity_id = ".$quote_Id;
            $connection->query($sql);
        }
    }

    public function getCheckoutSession(){
        $checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');
        return $checkoutSession;
    }

    public function getItemModel(){
        $itemModel = $this->_objectManager->create('Magento\Quote\Model\Quote\Item');
        return $itemModel;
    }

}