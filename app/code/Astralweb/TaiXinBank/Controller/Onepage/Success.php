<?php

namespace Astralweb\TaiXinBank\Controller\Onepage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;

class Success extends \Magento\Checkout\Controller\Onepage\Success {

    protected $invoiceSender;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, CustomerRepositoryInterface $customerRepository, AccountManagementInterface $accountManagement, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\Translate\InlineInterface $translateInline, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\View\LayoutFactory $layoutFactory, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Magento\Framework\Controller\Result\RawFactory $resultRawFactory, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, InvoiceSender $invoiceSender)
    {
        $this->invoiceSender = $invoiceSender;
        parent::__construct($context, $customerSession, $customerRepository, $accountManagement, $coreRegistry, $translateInline, $formKeyValidator, $scopeConfig, $layoutFactory, $quoteRepository, $resultPageFactory, $resultLayoutFactory, $resultRawFactory, $resultJsonFactory);
    }

    public function execute()
    {

        $session = $this->getOnepage()->getCheckout();
        $paymentMethod = $this->_objectManager->create('Magento\Quote\Model\Quote')->load($session->getLastQuoteId())->getPayment()->getMethod();
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')
            ->load($session->getLastOrderId());

        $helper = $this->_objectManager->create('Astralweb\TaiXinBank\Helper\Data');
        $mid = $helper->getConfig('payment/taixinbank/mid');
        $tid = $helper->getConfig('payment/taixinbank/tid');
        $pay_type = $helper->getConfig('payment/taixinbank/pay_type');
        $tx_type = $helper->getConfig('payment/taixinbank/tx_type');
        $capt_flag = $helper->getConfig('payment/taixinbank/capt_flag');
        $result_flag = $helper->getConfig('payment/taixinbank/result_flag');
        $post_back_url = $helper->getConfig('payment/taixinbank/post_back_url');
        $result_url = $helper->getConfig('payment/taixinbank/result_url');
        $url = $helper->getConfig('payment/taixinbank/taixinbank_url');
        



        if($paymentMethod == 'taixinbank' && $order->getStatus() == 'pending'){
            $fields='{
                "sender":"rest",
                "ver":"1.0.0",
                "mid":"'.$mid.'",    
                "tid":"'.$tid.'",
                "pay_type":'.$pay_type.',
                "tx_type":'.$tx_type.',
                "params":
                    {
                    "layout":"1",
                    "order_no":"'.$order->getIncrementId().'",
                    "amt":"'.intval($order->getGrandTotal()*100).'",
                    "cur":"NTD",
                    "order_desc":"測試 3C 網站購物",
                    "capt_flag":"'.$capt_flag.'",
                    "result_flag":"'.$result_flag.'",
                    "post_back_url":"'.$post_back_url.'",
                    "result_url":"'.$result_url.'"
                }
            }';



            $process = curl_init($url);
            curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($process, CURLOPT_HEADER, 0);
            curl_setopt($process, CURLOPT_TIMEOUT, 60);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($process, CURLOPT_POSTFIELDS,$fields);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
            $resultjson = curl_exec($process);
            curl_close($process);
            $result = json_decode($resultjson);
              $data_response = $fields;
            $this->directory_list = $this->_objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');
             $folderExist = is_dir($this->directory_list->getPath('var') . '/log/taixinbank/');
            if (!$folderExist) {
                mkdir($this->directory_list->getPath('var') . '/log/taixinbank/', 0755, true);
            }
             file_put_contents($this->directory_list->getPath('var') . '/log/taixinbank/'.$order->getIncrementId().'_request_'. date('Y-m-d H:i:s') .'.log', $data_response, FILE_APPEND);

            file_put_contents($this->directory_list->getPath('var') . '/log/taixinbank/'.$order->getIncrementId().'_response_first_'. date('Y-m-d H:i:s') .'.log', $resultjson, FILE_APPEND);


            $retCode = $result->params->ret_code;
          
            

           
            
           

            if($retCode == "00"){
                $urlsendbank =  $result->params->hpp_url;
                return $this->_redirect($urlsendbank);
            }else{
                return $this->_redirect('astralwebtaixinbankpayment/index/bankfailt');
            }
    
        }

        if ($paymentMethod == 'cashondelivery' && $order->getStatus() == 'pending_cod') {
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

//                $order->addStatusHistoryComment(
//                    __($parmas['ret_msg'])
//                )
                    // ->setIsCustomerNotified(true)

                $order->setState('processing');
                $order->setStatus('processing');
                $order->save();
                $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $resultRedirect = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore($storeManager->getStore()->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB).'checkout/onepage/success';
                // $this->deleteQuoteItems();
                return $this->_redirect($resultRedirect);
            }
        }
        if (!$this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator')->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        $session->clearQuote();
        //@todo: Refactor it to match CQRS
        $resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => [$session->getLastOrderId()]]
        );
        return $resultPage;
    }
   
}
