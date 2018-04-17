<?php


namespace Astralweb\Invoicetype\Model\ResourceModel\InvoiceType;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Astralweb\Invoicetype\Model\InvoiceType','Astralweb\Invoicetype\Model\ResourceModel\InvoiceType');
    }
}