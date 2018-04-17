<?php


namespace Astralweb\Invoicetype\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
class InvoiceType extends AbstractDb
{
    public function _construct()
    {
        // TODO: Implement _construct() method.
        $this->_init('astralweb_invoicetype','id');
    }
}