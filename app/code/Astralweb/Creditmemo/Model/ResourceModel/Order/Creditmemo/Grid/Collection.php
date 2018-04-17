<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Astralweb\Creditmemo\Model\ResourceModel\Order\Creditmemo\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Collection
{

    protected function _construct()
{
    parent::_construct();
    $this->addFilterToMap('entity_id','main_table.entity_id');
    $this->_map['fields']['increment_id'] = 'main_table.increment_id';
    $this->_map['fields']['state'] = 'main_table.state';
}
    protected function _renderFiltersBefore()
    {
      $this->getSelect()->join(
                ['store_table' => $this->getTable('sales_creditmemo')],
                'main_table.entity_id = store_table.entity_id',
                ['entity_id'=> 'main_table.entity_id' ,'product_custom' => 'store_table.product_custom','telephone_billing' => 'store_table.telephone_billing','telephone_shipping' => 'store_table.telephone_shipping']
            )->group(
                'main_table.entity_id'
            );;
      parent::_renderFiltersBefore();
    }
 }
