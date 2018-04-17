<?php
namespace Astralweb\ShippingStorePickUp\Controller\Adminhtml\Index;

class ImportAction extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $_orderAction;
    protected $_csvProcessor;
    protected $_order;
    protected $_helperData;
    protected $_convertOrder;
    protected $_orderShipmentTrackingModel;
    protected $_shipmentNotifier;
    protected $_trackFactory;
    protected $_resource;
    protected $_moduleManager;
    protected $_helperDataCvs;
    protected $_shopFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\Order\Shipment\Track $orderShipmentTrackingModel,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Astralweb\Shippingsf\Helper\Data $helperData,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Astralweb\Shippingcvs\Helper\Data $helperDataCvs,
        \Astralweb\ShippingStorePickUp\Model\shopFactory $shopFactory,
        \Magento\Framework\Module\Manager $moduleManager
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_csvProcessor = $csvProcessor;
        $this->_helperData = $helperData;
        $this->_convertOrder = $convertOrder;
        $this->_order = $order;
        $this->_trackFactory = $trackFactory;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_orderShipmentTrackingModel =$orderShipmentTrackingModel;
        $this->_resource = $resource;
        $this->_moduleManager = $moduleManager;
        $this->_helperDataCvs = $helperDataCvs;
        $this->_shopFactory = $shopFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $form_key = $this->getRequest()->getParam("form_key");
        $orderError = array();
 
        if($form_key) {
            if (isset($_FILES['file'])) {
                if (!isset($_FILES['file']['tmp_name'])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
                }

                // 全刪除
                $collection = $this->_objectManager->create('Astralweb\ShippingStorePickUp\Model\ResourceModel\shop\Collection');
                $collection->addFieldToSelect('*')->load();
                $collection->walk('delete');

                $importProductRawData = $this->_csvProcessor->getData($_FILES['file']['tmp_name']);
                foreach ($importProductRawData as $rowIndex => $dataRow) {

                    // if ($dataRow[0] == 'order_increment') continue;
                    if ($rowIndex == 0 ) continue;
 
                    $model = $this->_shopFactory->create();
                    $model->setData('county'    , $dataRow[0]); // 新竹市,
                    $model->setData('region'    , $dataRow[1]); // 東區,   
                    $model->setData('code'      , $dataRow[2]); // 1001,    
                    $model->setData('name'      , $dataRow[3]); // 新竹門市,
                    $model->setData('street'    , $dataRow[4]); // 中正路8號
                    $model->setData('telephone' , $dataRow[5]); // 電話
                    $model->setData('postcode'  , $dataRow[6]); // 郵遞區號
                    $model->save();
                }
                $resultPage->getConfig()->getTitle()->prepend(__('Import Success'));
            }
        }

        $this->_redirect('*/*/index/');
    }
}
