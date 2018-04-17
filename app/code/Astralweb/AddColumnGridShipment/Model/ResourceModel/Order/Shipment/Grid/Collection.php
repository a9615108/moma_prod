<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Astralweb\AddColumnGridShipment\Model\ResourceModel\Order\Shipment\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection
{
	 protected function _construct()
{
    parent::_construct();
    $this->addFilterToMap('entity_id','main_table.entity_id');
}
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinLeft(
            ['rela'=>$this->getTable("sales_shipment_track")],
            'rela.order_id = main_table.order_id',
            ['track_number' => new \Zend_Db_Expr('group_concat(rela.track_number SEPARATOR ", ")')]
        )->group('main_table.order_id');
        parent::_renderFiltersBefore();
    }
}
