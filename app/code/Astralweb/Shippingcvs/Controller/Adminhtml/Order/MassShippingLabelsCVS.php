<?php
namespace Astralweb\Shippingcvs\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassShippingLabelsCVS extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
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
        $ids = $collection->getAllIds();
        $arrId = $ids;
        $countIds = count($ids);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $countryFactory = $objectManager->get('Magento\Directory\Model\CountryFactory');
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $table  = $resource->getTableName('astralweb_shippingcvs');
        $helperData = $objectManager->get('Astralweb\Shippingcvs\Helper\Data');
        $directory = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $libmpdf = $directory->getRoot().'/lib/internal/01-mPDF-v6.1.0/mpdf.php';
        $cssfile = $directory->getRoot().'/lib/internal/01-mPDF-v6.1.0/styles-pickup.css';
        $orderRespository = $objectManager->get('Magento\Sales\Api\OrderRepositoryInterface');
        foreach ($ids as $orderid) {
           $order = $orderRespository->get($orderid);
           $shippingMethod = $order->getData('shipping_method');
            if($shippingMethod != 'collect_storecvs_collect_storecv'){
                $key = array_search($orderid, $arrId);
               unset($arrId[$key]);
            }

        }
        if(empty($arrId)){
            $this->messageManager->addError(__('Not have order print labels CVS'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->getComponentRefererUrl());
            return $resultRedirect;

        }
            include ($libmpdf);
        
        $stylesheet = file_get_contents($cssfile); // Get css content

        $mpdf=new \mPDF('utf-8','A4-L');
        $mpdf->autoLangToFont = true;
        $mpdf->WriteHTML($stylesheet,1);

        $i=1;
        $htmlPickup ='';
        foreach ($arrId as $orderid){
            if($i <= count($ids)){
                if($i%4 == 1){
                    $htmlPickup .= $helperData->getPickupHtml(1,$orderid);
                }elseif($i%4 == 2){
                    $htmlPickup .= $helperData->getPickupHtml(2,$orderid);
                }elseif ($i%4 == 3){
                    $htmlPickup .= $helperData->getPickupHtml(3,$orderid);
                }elseif ($i%4 == 0){
                    $htmlPickup .= $helperData->getPickupHtml(4,$orderid);
                    if(count($ids) > 4) {
                        $html = $helperData->getHtml($htmlPickup);
                        $mpdf->WriteHTML($html);
                        $mpdf->AddPage('utf-8','A4-L');
                        $htmlPickup='';
                    }else{
                        $html = $helperData->getHtml($htmlPickup);
                        $mpdf->WriteHTML($html);
                        $mpdf->autoScriptToLang = true;
                        $mpdf->baseScript = 1;
                        $mpdf->autoVietnamese = true;
                        $mpdf->autoArabic = true;
                        $mpdf->allow_charset_conversion = true;
                        $mpdf->Output('doanhtest.pdf','D');

                    }
                }
            }

            $i++;

        }
        $html = $helperData->getHtml($htmlPickup);
        $mpdf->WriteHTML($html);
        $mpdf->autoScriptToLang = true;
        $mpdf->baseScript = 1;
        $mpdf->autoVietnamese = true;
        $mpdf->autoArabic = true;
        $mpdf->allow_charset_conversion = true;
        $mpdf->Output(time().'.pdf','D');


    }
}
