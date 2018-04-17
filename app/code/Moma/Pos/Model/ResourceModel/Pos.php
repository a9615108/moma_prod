<?php


namespace Moma\Pos\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
class Pos extends AbstractDb
{
    public function _construct()
    {
        // TODO: Implement _construct() method.
        $this->_init('moma_pos','id');
    }
}