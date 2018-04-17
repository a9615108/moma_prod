<?php
namespace Astralweb\ShippingStorePickUp\Model\ResourceModel\shop;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Astralweb\ShippingStorePickUp\Model\shop','Astralweb\ShippingStorePickUp\Model\ResourceModel\shop');
    }
}
