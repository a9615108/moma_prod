<?php
namespace Astralweb\Contactus\Model;
class Contact extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = '';

    protected function _construct()
    {
        $this->_init('Astralweb\Contactus\Model\ResourceModel\Contact');
    }

}
