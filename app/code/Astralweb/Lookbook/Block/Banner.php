<?php

namespace Astralweb\Lookbook\Block;

class Banner extends \AstralWeb\BannerManagement\Block\Banner
{
       public function getBannerData($position)
     {
         $return = array();
         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         $collections = $objectManager->create('AstralWeb\BannerManagement\Model\Banner')->getCollection();
         $this->astralweb_banner = $this->_resourceConnect->getTableName('astralweb_banner');
         $this->astralweb_banner_store = $this->_resourceConnect->getTableName('astralweb_banner_store');

         $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
         $category = $this->_coreRegistry->registry('current_category');
         $cmsPage = $objectManager->get('\Magento\Cms\Model\Page');
         $collections->addFieldToFilter('position', $position);

         $collections->addFieldToFilter('status', 1);
         $collections->addStoreFilter($this->_storeManager->getStore()->getId());
         foreach ($collections as $collection) {
             if (isset($category)) {
                 $id = $category->getId();
                 $keypages = unserialize($collection->getCategories());
             } elseif (isset($cmsPage) && $cmsPage->getId()) {
                 $id = $cmsPage->getId();
                 $keypages = unserialize($collection->getCmsPages());
                 //$collections->addFieldToFilter('cmsPage', 1);
             } elseif ($requestInterface->getFullActionName() == "catalogsearch_result_index") {
                 $id = 'result_index';
                 $keypages = unserialize($collection->getSearchResults());
             }
             if (isset($id) && isset($keypages) && $keypages && $id) {
                 if (in_array($id, $keypages)) {
                 $return[] = $collection;
                 }
             }
         }
         return $return;
     }
    protected function _toHtml()
    {
        $this->setModuleName($this->extractModuleName('AstralWeb\BannerManagement\Block\Banner'));
        return parent::_toHtml();
    }

  
}
