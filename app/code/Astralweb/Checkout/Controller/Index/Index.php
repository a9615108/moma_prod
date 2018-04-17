<?php

namespace Astralweb\Checkout\Controller\Index;

class Index extends \Magento\Checkout\Controller\Index\Index {

    /**
     * Checkout page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 判斷是否登入
        $addresss = $this->_objectManager->create('\Magento\Customer\Model\Address')->getCollection();
        $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
        $addresss->addFieldToFilter('parent_id', $customerSession->getId());
        $addresss->getFirstItem();
        if (!$addresss->getData()) {
            $this->messageManager->addError(__('未偵測到任何地址，請先新增預設地址至帳戶內，完成註冊流程。'));
            return $this->resultRedirectFactory->create()->setPath('customer/address');
        }

        /** @var \Magento\Checkout\Helper\Data $checkoutHelper */
        $checkoutHelper = $this->_objectManager->get('Magento\Checkout\Helper\Data');
        if (!$checkoutHelper->canOnepageCheckout()) {
            $this->messageManager->addError(__('One-page checkout is turned off.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        // hasItems : 判斷購物車內有無商品
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

/* moma-19 */
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/POS_PROD_STORE.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $productRepository = $this->_objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface');

    // 抓購物車內的商品
        $items = $quote->getItems();
        $car_arr = array();
        if( $items ){
            foreach($items as $item) {
                // 判斷要不要查詢庫存
                $product = $productRepository->get($item->getSku());
                $optionId = $product->getData('is_connect_pos');
                $attr = $product->getResource()->getAttribute('is_connect_pos');
                $optionText = $attr->getSource()->getOptionText($optionId);
                if( $optionText == 1 || $optionText == '' ){
                    $car_arr[$item->getSku()] = array('Qty'=>$item->getQty());
                }
            }
        }
        $query_str = join(',',array_keys( $car_arr));

    // 抓庫存
        $response = array();
        if($query_str != ''){
            $POS_PROD_STORE = $this->_objectManager->get('Magento\Variable\Model\Variable')
                                   ->loadByCode('POS_PROD_STORE')
                                   ->getName();
            $url = $POS_PROD_STORE.'?SKU='.$query_str;
            $logger->info($url);
            $response = @file_get_contents($url);
            $logger->info($response);

            if($response === false) {
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }
            $response = json_decode($response,true);
            if( ! is_Array( $response ) ){
                $response = array();
            }
        }

    // 庫存資料存入資料庫
        $stockRegistry = $this->_objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
        foreach( $response as $row ){
            $product = $productRepository->get($row['SKU']);
            $stockItem = $stockRegistry->getStockItem($product->getId());
            $stockItem->setData('qty',$row['Qty']);
            $stockRegistry->updateStockItemBySku($row['SKU'], $stockItem);
        }

    // 檢查
        foreach( $response as $row ){
            if( $car_arr[$row['SKU']]['Qty'] > $row['Qty'] ){
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }
        }
/**/
        if ( $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->_customerSession->isLoggedIn() && !$checkoutHelper->isAllowedGuestCheckout($quote)) {
            $this->messageManager->addError(__('Guest checkout is disabled.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $this->_customerSession->regenerateId();
        $this->_objectManager->get('Magento\Checkout\Model\Session')->setCartWasUpdated(false);
        $this->getOnepage()->initCheckout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Checkout'));
        return $resultPage;
    }
}