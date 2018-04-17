<?php

namespace Astralweb\ShippingStorePickUp\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('sales_order_grid'), 
                    'shop_id', 
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 
                        'length' => 10, 
                        'nullable' => true, 
                        'comment' => 'Shop ID' 
                    ]
                );

            $setup->endSetup();
        }
    }
}