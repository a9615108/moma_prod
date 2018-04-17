<?php
namespace Astralweb\Contactus\Model\ResourceModel\Contact;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Astralweb\Contactus\Model\Contact','Astralweb\Contactus\Model\ResourceModel\Contact');
    }
}
