<?php

namespace Astralweb\Lookbook\Block;

class Lookbook extends \Magento\Framework\View\Element\Template
{
  public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data=[]
	){
    $this->_categoryFactory = $categoryFactory;
    $this->_productCollectionFactory = $productCollectionFactory;
		parent::__construct($context, $data);

	}

    protected function _prepareLayout()
    {

        $this->getLayout()->createBlock('Magento\Catalog\Block\Breadcrumbs');
        return $this;
    }


    public function getImageProduct()
  {
    $imageData = array();
    $categoryId = (int)$this->getRequest()->getParam('id', false);
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
    if(!$collection->getSize()){
      return false;
    }
    $k = -1;
    foreach ($collection as $value) {
      $this->_objectManager = \Magento\Framework \App\ObjectManager::getInstance();
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
    $categoryId = (int)$this->getRequest()->getParam('id', false);
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
