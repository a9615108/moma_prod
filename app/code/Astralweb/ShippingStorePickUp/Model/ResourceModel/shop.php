<?php
namespace Astralweb\ShippingStorePickUp\Model\ResourceModel;
class shop extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('astralweb_shippingstorepickup_shop','astralweb_shippingstorepickup_shop_id');
    }
}
