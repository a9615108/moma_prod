<?php
namespace Moma\Pos\Cron;

class Order
{

    protected $_posfactory;
    protected $_helper;

    public function __construct(\Moma\Pos\Model\PosFactory $posfactory, \Moma\Pos\Helper\Data $helper
    )
    {
       $this->_posfactory =  $posfactory;
       $this->_helper = $helper;
    }

    public function execute() {
        $this->_helper->saveOrderPOS();
    }

}