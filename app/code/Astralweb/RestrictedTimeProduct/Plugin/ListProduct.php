<?php

namespace Astralweb\RestrictedTimeProduct\Plugin;
 
 
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct{

    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            /* @var $layer \Magento\Catalog\Model\Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if ($this->_coreRegistry->registry('product')) {
                // get collection of categories this product is associated with
                $categories = $this->_coreRegistry->registry('product')
                    ->getCategoryCollection()->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                try {
                    $category = $this->categoryRepository->get($this->getCategoryId());
                } catch (NoSuchEntityException $e) {
                    $category = null;
                }

                if ($category) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
                $now = new \DateTime();
             $this->_productCollection->addAttributeToFilter('enable_product_from', [['null' => false],
['lteq' => $now->format('Y-m-d H:i:s')]])
                   ->addAttributeToFilter('enable_product_to', [['null' => false],['gteq' => $now->format('Y-m-d H:i:s')]]);
        }

        return $this->_productCollection;
    }
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();
        
        $now = new \DateTime();
        $collectionNew1 =    $collection->addAttributeToFilter('enable_product_from', [['null' => false],
['lteq' => $now->format('Y-m-d H:i:s')]])
                   ->addAttributeToFilter('enable_product_to', [['null' => false],['gteq' => $now->format('Y-m-d H:i:s')]]);
       // $collectionNew = clone $collectionNew1;
      //  var_dump($collectionNew1->getData());
        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collectionNew1);

        $this->setChild('toolbar', $toolbar);
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->_getProductCollection()]
        );
        //var_dump($collection->getSize());
        $this->_getProductCollection()->load();
        //var_dump(count($collection));die;
        return $this;
    }



}