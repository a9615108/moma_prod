<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   1.1.22
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $installer->getConnection()->changeColumn(
                $installer->getTable('mst_rma_status'),
                'is_rma_resolved',
                'is_show_shipping',
                'TINYINT(1)'
            );
        }
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mst_rma_order_status_history')
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'primary' => true],
                'Order Id'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                32,
                ['unsigned' => false, 'nullable' => false],
                'Status'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => false, 'nullable' => false],
                'Created At'
            );
            $installer->getConnection()->createTable($table);
        }
        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mst_rma_return_address')
            )->addColumn(
                'address_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
                'Return Address Id'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                128,
                ['unsigned' => false, 'nullable' => false],
                'Return Address'
            )->addColumn(
                'address',
                Table::TYPE_TEXT,
                512,
                ['unsigned' => false, 'nullable' => false],
                'Return Address'
            )->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'default' => 0],
                'Sort Order'
            )->addColumn(
                'is_active',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'default' => 0],
                'Is Active'
            );
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addColumn(
                $installer->getTable('mst_rma_rma'),
                'return_address',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'   => 1024,
                    'nullable' => true,
                    'comment'  => 'Return Address'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $installer->getConnection()->dropTable($installer->getTable('mst_rma_order_status_history'));
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mst_rma_order_status_history')
            )->addColumn(
                'history_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
                'Order Id'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'primary' => false],
                'Order Id'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                32,
                ['unsigned' => false, 'nullable' => false],
                'Status'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => false, 'nullable' => false],
                'Created At'
            );
            $installer->getConnection()->createTable($table);
        }
    }
}
