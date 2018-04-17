<?php

namespace Astralweb\Lookbook\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Transaction;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Checkout\Model\Cart as CustomerCart;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{   


      public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Catalog\Model\CategoryFactory $categoryFactory,
    \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->_categoryFactory = $categoryFactory;
      $this->_storeManager = $storeManager;
    $this->_productCollectionFactory = $productCollectionFactory;
    $this->_coreRegistry = $coreRegistry;
    }


    public function getImageProduct()
    {
    $this->_objectManager = \Magento\Framework \App\ObjectManager::getInstance();
     $categoryId = $this->_coreRegistry->registry('current_category')->getId();
     $categoryLookbook = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
      if(!$categoryLookbook->getLookbook()){
        return false;
      }

    $imageData = array();
    
    if(!$categoryId){
      return false;
    }
    $category = $this->_categoryFactory->create()->load($categoryId);
    $collection = $this->_productCollectionFactory->create();
    $collection->addAttributeToSelect('image');
    $collection->addAttributeToSelect('lookbook_image');
    $collection->addAttributeToSelect('status');
    $collection->addAttributeToSelect('url_key');
    $collection->addCategoryFilter($category);
    $collection->addAttributeToFilter('visibility', array('neq' =>  \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE));
    $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
    $collection->setOrder('position','ASC');
    if(!$collection->getSize()){
      return false;
    }
    $k = -1;
    foreach ($collection as $value) {
      
      $_product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($value->getId());
      $lookbook1 = $_product->getData('lookbook1');
      if(isset($lookbook1) && $_product->getData('lookbook1') != 'no_selection'){
        $k = $k+1;
        $imageData['image'][$k] = $_product->getData('lookbook1');
        $imageData['url'][$k] = $value->getProductUrl();
      }
      $lookbook2 = $_product->getData('lookbook2');
      if(isset($lookbook2) &&  $_product->getData('lookbook2') != 'no_selection'){
        $k = $k+1;
        $imageData['image'][$k] = $_product->getData('lookbook2');
        $imageData['url'][$k] = $value->getProductUrl();
      }
      $lookbook3 = $_product->getData('lookbook3');
      if(isset($lookbook3) &&  $_product->getData('lookbook3') != 'no_selection'){
        $k = $k+1;
        $imageData['image'][$k] = $_product->getData('lookbook3');
        $imageData['url'][$k] = $value->getProductUrl();
      }
      $lookbook4 = $_product->getData('lookbook4');
      if(isset($lookbook4) && $_product->getData('lookbook4') != 'no_selection'){
        $k = $k+1;
        $imageData['image'][$k] = $_product->getData('lookbook4');
        $imageData['url'][$k] = $value->getProductUrl();
      }

    }

    return $imageData;
  }

  public function getImagecategory(){
    // $categoryId = (int)$this->getRequest()->getParam('id', false);
    $categoryId = 99;
    if(!$categoryId){
      return false;
    }
    $category = $this->_categoryFactory->create()->load($categoryId);
    if($category->getImage()){
      return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/category/'. $category->getImage();
    }
    return false;
  }

  public function getBaseUrlMedia($path = '', $secure = false)
   {
       return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'. $path;
   }

}
