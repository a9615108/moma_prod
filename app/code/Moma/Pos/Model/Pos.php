<?php


namespace Moma\Pos\Model;

use Magento\Framework\Model\AbstractModel;

class Pos extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Moma\Pos\Model\ResourceModel\Pos');
    }
}