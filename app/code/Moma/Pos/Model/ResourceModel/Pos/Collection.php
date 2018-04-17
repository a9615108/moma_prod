<?php


namespace Moma\Pos\Model\ResourceModel\Pos;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Moma\Pos\Model\Pos','Moma\Pos\Model\ResourceModel\Pos');
    }
}