<?php
namespace Astralweb\Contactus\Model\ResourceModel;
class Contact extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('Astralweb_contactus','id');
    }
}
