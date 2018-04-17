<?php
namespace Astralweb\ShippingStorePickUp\Model;
class shop extends \Magento\Framework\Model\AbstractModel implements \Astralweb\ShippingStorePickUp\Api\Data\shopInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'astralweb_shippingstorepickup_shop';

    protected function _construct()
    {
        $this->_init('Astralweb\ShippingStorePickUp\Model\ResourceModel\shop');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
