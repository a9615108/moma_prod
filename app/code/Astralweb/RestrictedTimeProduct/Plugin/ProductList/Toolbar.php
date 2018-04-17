<?php

namespace Astralweb\RestrictedTimeProduct\Plugin\ProductList;
 
 
class Toolbar{
	protected $_collectionNew = null;
	 public function beforeSetCollection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject,$collection)
    {
       
       $this->_collectionNew = $collection->getSize();
        return [$collection];
    }
     public function afterGetTotalNum(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject,$result)
    {
    	return $this->_collectionNew;
    }


} 