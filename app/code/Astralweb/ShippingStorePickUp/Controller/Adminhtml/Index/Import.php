<?php
namespace Astralweb\ShippingStorePickUp\Controller\Adminhtml\Index;
class Import extends \Magento\Backend\App\Action
{
    //const ADMIN_RESOURCE = 'Index';       

    protected $resultPageFactory;
    public function __construct(
//         \Astralweb\ShippingStorePickUp\Model\shopFactory $shopFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;        
        parent::__construct($context);
    }
    
    public function execute()
    {

// echo __file__;
// exit;
        return $this->resultPageFactory->create();  
    }
}
