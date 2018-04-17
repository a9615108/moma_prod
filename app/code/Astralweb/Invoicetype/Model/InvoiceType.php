<?php


namespace Astralweb\Invoicetype\Model;

use Magento\Framework\Model\AbstractModel;

class InvoiceType extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Astralweb\Invoicetype\Model\ResourceModel\InvoiceType');
    }
}