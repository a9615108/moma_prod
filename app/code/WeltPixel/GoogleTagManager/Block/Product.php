<?php
namespace WeltPixel\GoogleTagManager\Block;

/**
 * Class \WeltPixel\GoogleTagManager\Block\Product
 */
class Product extends \WeltPixel\GoogleTagManager\Block\Core
{
    /**
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|null
     */
    public function getRelatedProductCollection()
    {
        /** @var \Magento\Catalog\Block\Product\ProductList\Related $relatedProductListBlock */
        $relatedProductListBlock = $this->_layout->getBlock('catalog.product.related');

        if (empty($relatedProductListBlock)) {
            return null;
        }

        $collection = $relatedProductListBlock->getItems();
        return $collection;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|null
     */
    public function getUpsellProductCollection()
    {
        /** @var \Magento\Catalog\Block\Product\ProductList\Upsell $upsellProductListBlock */
        $upsellProductListBlock = $this->_layout->getBlock('product.info.upsell');

        if (empty($upsellProductListBlock)) {
            return null;
        }

        $collection = $upsellProductListBlock->getItemCollection()->getItems();
        return $collection;
    }
}