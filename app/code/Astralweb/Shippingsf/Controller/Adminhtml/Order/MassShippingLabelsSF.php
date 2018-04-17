<?php
namespace Astralweb\Shippingsf\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassShippingLabelsSF extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {

        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
    }


    protected function massAction(AbstractCollection $collection)
    {
        //Create Objetct Mpdf
        //Genfile pdf
        $datelog = date("Y-m-d h:i:sa", time());
        $ids = $collection->getAllIds();
        $countIds = count($ids);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directory = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $table  = $resource->getTableName('astralweb_shippingsf');
        $helperData = $objectManager->get('Astralweb\Shippingsf\Helper\Data');
        $html ="";
        $countError = 0;
        foreach ($ids as $orderid){
            //Get order data bind html
             $skus = array();
                         $countItems = 0;

            $order = $objectManager->get('Magento\Sales\Api\OrderRepositoryInterface')->get($orderid);
                            $orderIncrementId = $order->getIncrementId();
            foreach ($order->getItems() as $item) {
                if($item->getProductType() == 'simple'){
                    $skus[]=$item->getSku();
                    $countItems += (int) $item->getData('qty_ordered');

                }
            }
            $dataLine5 = $orderIncrementId.',共 '.$countItems.' 件,'.implode(',',$skus);
                    $countryFactory = $objectManager->get('Magento\Directory\Model\CountryFactory');
            $shippingMethod = $order->getShippingMethod();
            $state = $order->getState();
             $shippingAddress = $order->getShippingAddress();
           $nameShipping = $shippingAddress->getData('firstname').$shippingAddress->getData('lastname');
            $streetShipping = $shippingAddress->getData('street');
            $phoneShipping = $shippingAddress->getData('telephone');
            $region = $shippingAddress->getData('region');
            $city = $shippingAddress->getData('city');
            $countryId = $shippingAddress->getData('country_id');
            $country= $countryFactory->create()->loadByCode($countryId);
            $countryName = $country->getName();
            if($region == NULL) $region ='';
            $dataLine3 = $nameShipping.' '.$phoneShipping.' '.$countryName.' '.$city.' '.$region.' '.$streetShipping;

            $payment = $order->getPayment();
            $paymentMethod = $payment->getMethodInstance()->getCode();
            $subtotal = $order->getGrandTotal();

            if($shippingMethod == 'collect_store_collect_store'){
                // Get mailno and $destcode
                $countError++;    
                $sql ="SELECT * FROM " . $table ." WHERE order_id =".$orderid;
                $result = $connection->fetchAll($sql);
                if(count($result) > 0) {
                    $mailno = $result[0]['mailno'];
                    $destcode = $result[0]['destcode'];
                }else{
                    $mailno = '';
                    $destcode = '';
                }


                $html .=  $helperData->getHtmlPdfSF($mailno,$destcode,$dataLine3,$paymentMethod,$subtotal,$dataLine5);
                $helperData->WritelogSF($datelog,$orderIncrementId,'Print Label SF','','','');
              
                //$mpdf->WriteHTML($html);
               // if($countIds == 1){
               //     $mpdf->Output('filename.pdf','D');
               // }else{
               //     $mpdf->AddPage();
               //     $countIds--;
               // }
            }

        }if($countError == 0){
            $this->messageManager->addError(__('Not have order print labels SF'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->getComponentRefererUrl());
            return $resultRedirect;
        }else{
            $apikey = 'fa508da0-22f9-427d-bec7-f8c8479b2e80';
            // $value = ;
            $postdata = http_build_query(
                array(
                    'apikey' => $apikey,
                    'value' => $html,
                    'PageWidth' => '60',
                    'PageHeight'=>'91'
                )
            );

            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
            );

            $context  = stream_context_create($opts);

// Convert the HTML string to a PDF using those parameters
            $result = file_get_contents('http://api.html2pdfrocket.com/pdf', false, $context);

// Save to root folder in website
            $filename = $directory->getPath('pub').'/sf/labels/'. time().'.pdf';
            file_put_contents($filename, $result);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($filename));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            ob_clean();
            flush();
            readfile($filename);

        }
         }
}
