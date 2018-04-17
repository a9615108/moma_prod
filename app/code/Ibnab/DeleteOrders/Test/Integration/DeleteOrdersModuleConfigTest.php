<?php

namespace Ibnab\DeleteOrders\Test\Integration;
use Magento\Framework\Component\ComponentRegistrar;
class DeleteOrdersModuleConfigTest extends \PHPUnit_Framework_TestCase{
    /*
    public function testNothing(){
        $this->fail(":)");
    }
     * 
     */
    public function testModuleIsRegisted(){
        $registar = new ComponentRegistrar();
        $this->assertArrayHasKey("Ibnab_DeleteOrders", $registar->getPaths(ComponentRegistrar::MODULE));
    }
}

