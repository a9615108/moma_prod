<?php
namespace Astralweb\ShippingStorePickUp\Controller\Adminhtml\Index;
class Index extends \Magento\Backend\App\Action
{
    //const ADMIN_RESOURCE = 'Index';       

    protected $resultPageFactory;
    public function __construct(
         \Astralweb\ShippingStorePickUp\Model\shopFactory $shopFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->shopFactory = $shopFactory;        
        $this->resultPageFactory = $resultPageFactory;        
        parent::__construct($context);
    }
    
    public function execute()
    {
if( 0 ){
        $collection = $this->_objectManager->create('Astralweb\ShippingStorePickUp\Model\ResourceModel\shop\Collection');
        $collection->addFieldToSelect('*')
            ->addFieldToFilter(
                'code',
                ['in' => array('code')]
            ) 
            ->load();
        $collection->walk('delete');
}
if( 0 ){
        $id = 2;

        $model = $this->shopFactory->create();
        $model->load($id);
        $model->delete();
}
if( 0 ){
        
        $model = $this->shopFactory->create();
        // $model->load($id);
        $model->setData('code', 'code 1');
        $model->save();

        $model = $this->shopFactory->create();
        $model->setData('code', 'code 2');
        $model->save();

        $model->setData('code', 'code 3');
        $model->save();
}
if( 0 ){
		$model = $this->shopFactory->create();
		$collection = $model->getCollection();
		foreach($collection as $item){
			echo "<pre>";
			print_r($item->getData());
			echo "</pre>";
		}
		exit();
}

		// return $this->_pageFactory->create();

// echo __file__;
// exit;
        return $this->resultPageFactory->create();  
    }
}
